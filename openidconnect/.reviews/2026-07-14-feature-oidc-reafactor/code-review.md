# Code Review — openidconnect addon tests

**Date**: 2026-07-14
**Files reviewed**: 
- tests/Provider/TokenClientTest.php
- tests/Provider/IdTokenValidatorTest.php
- tests/Account/AvatarUpdaterTest.php
- tests/Account/UserProvisionerTest.php
- tests/Presentation/AdminHookTest.php
- tests/stubs/DBA.php
- tests/stubs/Model.php
- tests/bootstrap.php
- tests/Support/AddonTestCase.php

**Reviewer**: AI Code Review Agent
**Artifact location**: `.reviews/2026-07-14-feature-oidc-reafactor/`
**Scope**: Security, correctness, brittle stubs

## Summary

Test suite demonstrates good coverage of error paths and edge cases for OIDC flows. Three **critical** issues found that could allow defects to reach production: missing JWT expiration test, transaction stub that doesn't simulate rollback, and insufficient retry validation. Two **major** security/correctness gaps found in admin config handling and DBA stub type coercion.

## Auto-Fixed Issues

No auto-fixes applied.

## Findings

### Critical

**[CR-001] Missing JWT expiration validation test**
- **File**: `tests/Provider/IdTokenValidatorTest.php`
- **Location**: All test methods
- **Issue**: Every JWT token in tests uses `'exp' => time() + 300` (always valid). No test verifies that expired tokens (`'exp' => time() - 300`) are properly rejected by `IdTokenValidator::validate()`.
- **Impact**: If JWT expiration checking regresses, tests won't catch it. This is a critical security boundary.
- **Fix**: Add `testRejectsExpiredToken()` that generates a token with `'exp' => time() - 300` and asserts `validate()` returns `false`.

**[CR-002] Transaction stub doesn't simulate rollback behavior**
- **File**: `tests/stubs/DBA.php`
- **Location**: Lines 98-101 — `transaction()` method
- **Issue**: Stub just executes the callable: `return $operation();`. No try-catch, no rollback simulation on exception.
- **Impact**: If production code relies on transaction rollback (e.g., `DBA::transaction(fn => ...)` throws and expects DB state to revert), tests will pass but production will fail. Masks critical data consistency bugs.
- **Fix**: Wrap in try-catch, restore `self::$users` and `self::$contacts` snapshots on exception:
  ```php
  public static function transaction(callable $operation): mixed
  {
      $usersSnapshot = self::$users;
      $contactsSnapshot = self::$contacts;
      try {
          return $operation();
      } catch (\Throwable $e) {
          self::$users = $usersSnapshot;
          self::$contacts = $contactsSnapshot;
          throw $e;
      }
  }
  ```

**[CR-003] Retry test doesn't validate correct retry sequence**
- **File**: `tests/Account/UserProvisionerTest.php`
- **Location**: Lines 110-129 — `testFindOrCreateRetriesCreateAfterDuplicateExceptionAndEventuallyCreatesUser()`
- **Issue**: Test uses closure that increments `$attempt` and throws on first call. However, it only asserts `$attempt >= 2`. Doesn't validate:
  1. First call threw the duplicate exception
  2. Second call succeeded
  3. Final result corresponds to the successful attempt
- **Impact**: Test could pass even if retry logic is broken (e.g., retries indefinitely, or doesn't retry at all but creates a different user).
- **Fix**: Assert exact sequence:
  ```php
  self::assertSame(2, $attempt, 'Expected exactly 1 retry after initial duplicate exception');
  self::assertSame('new-subject', $result['openid']);
  self::assertStringContainsString('newuser', $result['nickname']); // or exact match after collision resolution
  ```

### Major

**[CR-004] Admin secret preservation doesn't defend against empty-string attack**
- **File**: `tests/Presentation/AdminHookTest.php`
- **Location**: Lines 55-69 — `testSavePreservesExistingClientSecretWhenFieldIsOmitted()`
- **Issue**: Test validates that omitting `client_secret` from POST preserves the existing value. BUT: doesn't test sending `'client_secret' => ''` (empty string). If production code doesn't distinguish "omitted" from "explicitly empty", attacker could clear the secret.
- **Impact**: Potential secret erasure vulnerability if empty string is treated as "clear this value".
- **Fix**: Add test case:
  ```php
  public function testSaveRejectsEmptyClientSecretString(): void
  {
      DI::config()->set('openidconnect', 'client_secret', 'existing-secret');
      $hook = new AdminHook();
      $hook->save(['client_secret' => '']);
      
      // Should either preserve existing secret OR reject the save
      self::assertNotEmpty(DI::config()->get('openidconnect', 'client_secret'));
  }
  ```

**[CR-005] DBA stub `self` field comparison masks type mismatches**
- **File**: `tests/stubs/DBA.php`
- **Location**: Lines 46-49 — `selectFirst()` contact table handling
- **Issue**: Line 48 casts both sides to bool: `(bool)$condition['self'] !== (bool)($row['self'] ?? false)`. In production, DB returns `'0'`/`'1'` strings or `0`/`1` ints. Stub masks type coercion bugs if production code doesn't cast properly.
- **Impact**: Test passes with any truthy/falsy value, but production could fail with `'0'` string (truthy in PHP) vs `0` int (falsy).
- **Fix**: Match production behavior exactly — either always store as int, or always store as string, and compare without bool casting:
  ```php
  if (array_key_exists('self', $condition) && (int)$condition['self'] !== (int)($row['self'] ?? 0)) {
      continue;
  }
  ```

**[CR-006] Missing tests for JWT with absent required claims**
- **File**: `tests/Provider/IdTokenValidatorTest.php`
- **Location**: All test methods
- **Issue**: No tests for tokens missing `sub`, `iat`, or other required claims per OIDC spec. Production code might not validate these, and tests won't catch it.
- **Impact**: Malformed tokens could pass validation if claim presence checks are missing.
- **Fix**: Add tests:
  ```php
  public function testRejectsTokenMissingSubClaim(): void
  public function testRejectsTokenMissingIatClaim(): void
  ```

### Minor

**[CR-007] Error log assertions could pass with unrelated errors**
- **File**: `tests/Provider/TokenClientTest.php`
- **Location**: Lines 60-63, 79-82, 97-100, 115-118, 137-140, 154-157
- **Issue**: All error assertions use `array_key_last(DI::logger()->errors)` to check the message. If test setup logs an unrelated error, the assertion still checks the *last* error, which might not be from the code under test.
- **Impact**: False positive if error array isn't empty before test execution.
- **Fix**: Either clear errors in setUp, or count errors before/after:
  ```php
  $errorCountBefore = count(DI::logger()->errors);
  $result = (new TokenClient())->exchangeCode('bad-code');
  self::assertGreaterThan($errorCountBefore, count(DI::logger()->errors));
  self::assertSame('expected message', DI::logger()->errors[array_key_last(DI::logger()->errors)][0]);
  ```

**[CR-008] No test for max retry limit on duplicate constraints**
- **File**: `tests/Account/UserProvisionerTest.php`
- **Location**: Missing test case
- **Issue**: `testFindOrCreateRetriesCreateAfterDuplicateExceptionAndEventuallyCreatesUser` verifies that retries work, but no test for the case where duplicate exceptions keep happening (infinite loop protection).
- **Impact**: If retry loop has no limit, production could hang or exhaust resources.
- **Fix**: Add test that keeps throwing duplicates and verify the function eventually gives up:
  ```php
  public function testFindOrCreateGivesUpAfterMaxRetries(): void
  {
      User::$createHandler = static fn() => throw new \RuntimeException('SQLSTATE[23000]: duplicate');
      $result = $service->findOrCreate(...);
      self::assertNull($result); // or logs error
  }
  ```

**[CR-009] Avatar size limit test doesn't verify HEAD request path**
- **File**: `tests/Account/AvatarUpdaterTest.php`
- **Location**: Lines 56-60 — `testIsWithinDownloadSizeLimitTreatsUnknownOrInvalidContentLengthAsAllowed()`
- **Issue**: Test only covers "Content-Length not available" case. Doesn't test actual HEAD request with oversized Content-Length header.
- **Impact**: If `isWithinDownloadSizeLimit()` always returns true (broken), test won't catch it.
- **Fix**: Add test that mocks HTTP HEAD response with `Content-Length: 10000000` and asserts `isWithinDownloadSizeLimit()` returns false.

**[CR-010] Model stubs don't validate required fields**
- **File**: `tests/stubs/Model.php`
- **Location**: Lines 24-32 — `User::create()`
- **Issue**: Stub accepts any fields array without validating that required fields (nickname, email, etc.) are present. Production `User::create()` might fail with missing fields, but tests pass.
- **Impact**: Tests don't exercise validation logic.
- **Fix**: Add minimal field validation:
  ```php
  if (!isset($fields['nickname']) || !isset($fields['email'])) {
      throw new \InvalidArgumentException('nickname and email required');
  }
  ```

**[CR-011] AddonTestCase doesn't verify stub reset completeness**
- **File**: `tests/Support/AddonTestCase.php`
- **Location**: Lines 14-24 — `setUp()`
- **Issue**: Calls reset methods for known stubs, but no runtime check that all stubs actually reset. If a new stub is added but not reset here, state leaks between tests.
- **Impact**: Flaky tests, hard-to-debug failures.
- **Fix**: Consider adding a static registry of resetable stubs and assert all were called, or document this risk in comments.

## Code Smells

None identified — test structure is clean and follows good patterns.

## Positive

- **Excellent error path coverage**: TokenClient, IdTokenValidator, UserProvisioner all test failure modes thoroughly
- **Security-aware testing**: AdminHook tests read-only config enforcement, AvatarUpdater tests SSRF prevention
- **Good use of test stubs**: DI/DBA/Model stubs are well-structured and enable isolated testing
- **Test isolation**: AddonTestCase properly resets state between tests
- **Clear test names**: All tests use descriptive names that document expected behavior
