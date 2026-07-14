# Test Quality Review

**Date**: 2026-07-14

## Summary

Overall test quality is **high**. Test suite demonstrates strong understanding of failure modes and security boundaries. Excellent coverage of error paths (TokenClient, IdTokenValidator). Good isolation via stubs. Three critical gaps identified: missing JWT expiration test, transaction rollback simulation, and insufficient retry validation.

## Findings

### Behaviour vs. Implementation

**Good**: All tests assert on observable behavior (return values, side effects, logged messages) rather than internal implementation details. No tests spy on private methods or internal state that could change.

**Exception**: `IdTokenValidatorTest::testValidatorDoesNotUseStaticSharedRetryFlag()` (line 155) reads source code to verify absence of static variable. This is an anti-pattern — tests should verify behavior (e.g., retries work correctly), not implementation. If refactored, this test breaks unnecessarily.

**Recommendation**: Replace source-reading test with behavioral test that verifies retry behavior across multiple validator instances.

---

### Missing Edge Cases

**Critical gaps**:

1. **JWT expiration** (IdTokenValidatorTest): No test for `'exp' => time() - 300` (expired token). This is a security-critical boundary.
   
2. **JWT missing required claims** (IdTokenValidatorTest): No tests for tokens missing `sub`, `iat`, `nbf`, or other required OIDC claims.

3. **Max retry limit** (UserProvisionerTest): Tests retry success but not retry exhaustion. What happens if duplicate exceptions keep occurring?

4. **Empty string vs omitted field** (AdminHookTest): Tests omitted secret preservation but not `'client_secret' => ''` attack.

5. **Avatar Content-Length limit** (AvatarUpdaterTest): Tests "unknown size" path but not "known oversized" path (HEAD response with large Content-Length header).

6. **Token refresh** (TokenClientTest): No tests for `refresh_token` handling (if TokenClient supports it).

**Minor gaps**:

- No tests for concurrent account creation (race condition on nickname uniqueness)
- No tests for malformed JWT (invalid base64, corrupted signature)
- No tests for JWKS key rotation (kid mismatch scenarios)

---

### Brittle Tests

**DBA bool coercion** (`tests/stubs/DBA.php` line 48): 
```php
(bool)$condition['self'] !== (bool)($row['self'] ?? false)
```
This test stub accepts any truthy/falsy value, but production might store `'0'`/`'1'` strings. If production code doesn't cast properly, tests pass but production fails.

**Error log ordering assumption** (TokenClientTest, multiple locations):
```php
self::assertSame('expected', DI::logger()->errors[array_key_last(DI::logger()->errors)][0]);
```
Assumes the last error is from the code under test. If test setup logs an error, assertion could check wrong message.

**Transaction stub doesn't rollback** (`tests/stubs/DBA.php` line 99):
```php
public static function transaction(callable $operation): mixed
{
    return $operation();
}
```
No rollback on exception. If production code relies on transaction rollback, tests pass but production fails. This is a **critical test smell** that masks data consistency bugs.

---

### Parameterisation Opportunities

**IdTokenValidatorTest claim validation**:
```php
testRejectsIssuerMismatch()
testRejectsAudienceMismatch()
testRejectsNonceMismatch()
testRejectsAccessTokenHashMismatch()
```
These four tests follow identical structure: encode token with wrong claim, assert validation fails. Could be collapsed into:
```php
#[DataProvider('invalidClaimProvider')]
public function testRejectsInvalidClaims(array $claimOverrides, string $description): void
{
    $token = $this->createSignedIdToken($claimOverrides);
    self::assertFalse((new IdTokenValidator())->validate($token), $description);
}

public static function invalidClaimProvider(): array
{
    return [
        'issuer mismatch' => [['iss' => 'https://evil.example'], 'Wrong issuer should fail'],
        'audience mismatch' => [['aud' => ['other-client']], 'Wrong audience should fail'],
        // ... etc
    ];
}
```
Reduces duplication and makes it easier to add more claim validation tests.

---

### Missing Fixtures

**JWT material generation**: Every IdTokenValidatorTest method regenerates RSA keys, JWK, and configures cache. Should be a shared fixture:
```php
private string $privateKey;
private array $jwk;

protected function setUp(): void
{
    parent::setUp();
    [$this->privateKey, $this->jwk] = $this->generateRsaMaterial();
    DI::cache()->set('openidconnect:jwks', ['keys' => [$this->jwk]], 600);
}
```
Tests become much shorter and faster (key generation is expensive).

**User seeding**: UserProvisionerTest seeds users inline in each test. Could use a fixture builder or factory method for common user scenarios (linked user, unlinked user, user with specific email, etc.).

---

### Test Naming Issues

**Good**: All test names clearly describe expected behavior using the convention:
```
test<MethodName><Scenario>: void
```
Examples:
- `testExchangeCodeReturnsEmptyArrayWhenHttpClientThrows`
- `testIsSafeUrlRejectsNonHttpsThirdPartyHost`

No naming issues found.

---

### Test Isolation

**Good**: `AddonTestCase::setUp()` properly resets all stubs:
- `DBA::resetTestState()`
- `DI::resetTestState()`
- `User::resetTestState()`
- `Contact::resetTestState()`
- `SessionFunctionSpy::reset()`
- `CookieSpy::reset()`
- `$_GET`, `$_POST`, `$_SERVER` cleared

**Risk**: No runtime verification that all resetable stubs were actually reset. If a new stub is added but not listed in `setUp()`, state could leak between tests.

**Recommendation**: Consider stub registry pattern or explicit checklist in comments.

---

### Coverage Gaps

Based on test code review (without running coverage tool):

**Well-covered**:
- TokenClient error paths (HTTP failures, exceptions, malformed responses)
- IdTokenValidator happy path and some rejection paths
- AvatarUpdater SSRF prevention
- UserProvisioner account linking logic
- AdminHook config validation and read-only enforcement

**Likely under-covered** (need coverage tool to confirm):
- JWT expiration and missing claims (identified above)
- Retry limit exhaustion
- Concurrent operations (race conditions)
- Error recovery after partial failures

---

## Positive

**Excellent examples of test quality**:

1. **AdminHookTest::testSaveRespectsReadOnlyProviderFieldsButAllowsButtonText()**: Demonstrates sophisticated security testing — verifies that attacker-supplied values for static config fields are ignored while allowing editable fields. This is exactly the kind of test that prevents config override vulnerabilities.

2. **AvatarUpdaterTest SSRF prevention tests**: Comprehensive coverage of SSRF attack vectors (private IPs, DNS resolution failures, same-host exceptions). Shows deep security awareness.

3. **UserProvisionerTest::testFindOrCreateRejectsEmailMappedToDifferentLinkedSubject()**: Tests critical security boundary — ensures that account A's email can't be hijacked by account B's OIDC subject. Good threat modeling.

4. **TokenClientTest error logging**: Every error path verifies that appropriate error message is logged. This enables production debugging and demonstrates defensive coding.

These tests serve as good templates for the rest of the codebase.
