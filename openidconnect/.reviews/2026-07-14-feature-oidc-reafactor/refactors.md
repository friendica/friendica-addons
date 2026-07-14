# Refactor Proposals

**Date**: 2026-07-14

---

## [RF-001]: Extract RSA material generation into test helper trait

- **Type**: Extract method
- **Location**: `tests/Provider/IdTokenValidatorTest.php` — lines 180-208 (`generateRsaMaterial()` and `base64UrlEncode()`)
- **Problem**: RSA key generation is duplicated across multiple test classes that need to sign JWTs. Currently only in IdTokenValidatorTest but will be needed elsewhere.
- **Proposal**: Create `tests/Support/RsaKeyGenerator.php` trait with `generateRsaMaterial()` and `base64UrlEncode()`. Import in any test that needs JWT signing.
- **Effort**: S
- **Risk**: Low — pure test utility code
- **Status**: PROPOSED

---

## [RF-002]: Replace array-based field projection with typed DTOs

- **Type**: Improve abstraction
- **Location**: `tests/stubs/DBA.php` — `projectFields()` method and all callers
- **Problem**: Tests pass arrays everywhere (`['uid' => 5, 'email' => '...']`). No type safety, easy to typo field names. Production likely uses same pattern.
- **Proposal**: Introduce value objects (readonly classes):
  ```php
  readonly class UserRow {
      public function __construct(
          public int $uid,
          public string $email,
          public string $openid,
          public string $nickname,
      ) {}
  }
  ```
  Replace `selectFirst()` return type from `array` to `?UserRow`.
- **Effort**: M (affects production code + all tests)
- **Risk**: Medium (requires production code changes, not just test refactor)
- **Status**: PROPOSED

---

## [RF-003]: Consolidate error log assertion pattern into helper method

- **Type**: Remove duplication
- **Location**: `tests/Provider/TokenClientTest.php` — all test methods
- **Problem**: Same assertion pattern repeated 6 times:
  ```php
  self::assertNotEmpty(DI::logger()->errors);
  self::assertSame('expected message', DI::logger()->errors[array_key_last(DI::logger()->errors)][0]);
  ```
- **Proposal**: Add helper to `AddonTestCase`:
  ```php
  protected function assertLastErrorMatches(string $expectedMessage): void
  {
      self::assertNotEmpty(DI::logger()->errors, 'Expected at least one error to be logged');
      self::assertSame($expectedMessage, DI::logger()->errors[array_key_last(DI::logger()->errors)][0]);
  }
  ```
  Replace all call sites: `self::assertLastErrorMatches('openidconnect: ...');`
- **Effort**: S
- **Risk**: Low
- **Status**: PROPOSED

---

## [RF-004]: Extract JWT encoding boilerplate into test helper

- **Type**: Extract method
- **Location**: `tests/Provider/IdTokenValidatorTest.php` — all test methods that call `JWT::encode()`
- **Problem**: JWT encoding setup repeated in every test: generate keys, set config, encode with common claims structure. High noise-to-signal ratio.
- **Proposal**: Add to `AddonTestCase` or `RsaKeyGenerator` trait:
  ```php
  protected function createSignedIdToken(array $claims = []): string
  {
      [$privateKey, $jwk] = $this->generateRsaMaterial();
      DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);
      
      $defaults = [
          'iss' => 'https://id.example',
          'aud' => 'client-id',
          'sub' => 'subject-1',
          'iat' => time() - 5,
          'nbf' => time() - 5,
          'exp' => time() + 300,
      ];
      
      return JWT::encode(array_merge($defaults, $claims), $privateKey, 'RS256', $jwk['kid']);
  }
  ```
  Tests become: `$token = $this->createSignedIdToken(['iss' => 'https://evil.example']);`
- **Effort**: M
- **Risk**: Low
- **Status**: PROPOSED
