# OpenID Connect Addon Modularization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Decompose `openidconnect/openidconnect.php` (1,784 LOC) into cohesive PSR-4 classes below 200 LOC while preserving every route, hook, request/response side effect, configuration key, cache key, database write, and security check.

**Architecture:** Keep `openidconnect.php` as the Friendica-compatible procedural adapter: it loads Composer, exposes hook/module function names expected by Friendica, and delegates to a cached `OpenIdConnectAddon` facade. Move policy and transport logic into focused service classes under `src/`; services may obtain Friendica collaborators through `DI` at their boundary, matching the existing `webdav_storage/src` addon convention. Preserve runtime behaviour first; do not introduce migrations, feature flags, new configuration, or different authentication policy.

**Tech Stack:** PHP 8.1+ strict types, Friendica addon hooks/DI/DBA/Renderer, Composer PSR-4, `firebase/php-jwt` 7, PHPUnit 11.5.

## Global Constraints

- Every PHP file created by this change must use `declare(strict_types=1);` and a `Friendica\Addon\OpenIdConnect\...` namespace.
- `openidconnect/openidconnect.php` must be below 200 LOC after the final task; every `openidconnect/src/**/*.php` file must be below 200 LOC, measured by `wc -l`.
- Do not change public routes: `auth`, `callback`, `revoke`, `link`, `unlink`.
- Do not change hook names: `load_config`, `login_hook`, `logging_out`, `page_end`, `addon_settings`.
- Preserve global config keys, pconfig keys, cache keys, session key, state TTL, CSRF action, redirects, notices, and OIDC validation rules exactly.
- Preserve the existing outbound OAuth wire contract: authorization-code grant, PKCE S256, `client_secret_basic` preference, optional `client_secret_post`, 30-second token/userinfo timeout, and 15-second JWKS timeout.
- Preserve security controls: one-time cached state, nonce/`at_hash`/issuer/audience/`azp` validation, JWKS one-retry refresh, safe avatar URL policy, secure cookie policy, and POST+CSRF account unlink.
- Run `composer run qa` from `openidconnect/` after each task; add direct PHPUnit command for its focused test class.
- Keep commits focused. Never commit `vendor/`, `vendor-dev/`, generated Composer autoload files, credentials, tokens, or copied third-party code.

---

## File Structure and Responsibilities

| File | Responsibility | Target LOC |
| --- | --- | ---: |
| `openidconnect.php` | Composer bootstrap plus procedural Friendica hook/module adapters only | <200 |
| `src/OpenIdConnectAddon.php` | Factory, module route dispatch, and thin delegation wiring | <190 |
| `src/Lifecycle/AddonLifecycle.php` | Hook registration/unregistration and addon config loading | <170 |
| `src/Provider/ProviderConfiguration.php` | Required config check, discovery fetch/cache, client-auth selection | <180 |
| `src/Provider/TokenClient.php` | Authorization-code exchange and RFC 7009 token revocation | <180 |
| `src/Provider/IdTokenValidator.php` | JWKS retrieval/cache and ID-token cryptographic/claim checks | <195 |
| `src/Auth/AuthorizationRequest.php` | State/nonce/PKCE, redirect parameter build, state persistence | <190 |
| `src/Auth/CallbackHandler.php` | Callback control flow, state consumption, login/link completion | <195 |
| `src/Auth/LoginPolicy.php` | Return-path, bearer, transparent-login, and fallback policies | <170 |
| `src/Identity/UserInfo.php` | Userinfo endpoint request and ID-token claim normalization | <150 |
| `src/Account/AccountLinker.php` | Link lookup/write/unlink and unique-subject enforcement | <180 |
| `src/Account/UserProvisioner.php` | Existing-user lookup, email propagation, auto-link/create, nickname allocation | <195 |
| `src/Account/AvatarUpdater.php` | Safe remote avatar retrieval, temporary file lifecycle, Contact update | <180 |
| `src/Session/LogoutHandler.php` | Session token revocation, logout cookie, RP-initiated logout | <180 |
| `src/Route/AccountLinkRoutes.php` | Link/unlink route preconditions, CSRF, notice, redirects | <170 |
| `src/Presentation/LoginHook.php` | Login SSO button and transparent-SSO trigger | <150 |
| `src/Presentation/SettingsHook.php` | User settings panel rendering | <140 |
| `src/Presentation/AdminHook.php` | Admin config field metadata, rendering, POST save/cache flush | <195 |
| `src/Presentation/ModerationBadgeHook.php` | Moderation-user OIDC query and safe badge script output | <150 |
| `tests/Support/AddonTestCase.php` | Shared test reset/bootstrap utilities | <100 |
| `tests/{Auth,Provider,Identity,Account,Presentation}/*Test.php` | Focused regression tests by responsibility | <200 each |

## Dependency Direction

```text
Procedural entrypoint → OpenIdConnectAddon
OpenIdConnectAddon → Lifecycle + Route + Auth / Presentation services
AccountLinkRoutes → AuthorizationRequest + AccountLinker
CallbackHandler → AuthorizationRequest + TokenClient + IdTokenValidator + UserInfo + AccountLinker + UserProvisioner
TokenClient + IdTokenValidator + AuthorizationRequest → ProviderConfiguration
UserProvisioner → AccountLinker + AvatarUpdater
LogoutHandler → TokenClient + ProviderConfiguration
Presentation hooks → LoginPolicy / AccountLinker
```

No `src` class may call an `openidconnect_*` global function. This prevents a second procedural dependency graph from growing inside the new structure.

## Phase 0 — Baseline and Autoload Boundary

### Task 1: Establish a behaviour baseline and PSR-4 boundary

**Files:**
- Modify: `openidconnect/composer.json`
- Modify: `openidconnect/tests/bootstrap.php`
- Create: `openidconnect/tests/Support/AddonTestCase.php`
- Modify: `openidconnect/tests/OpenIdConnectTest.php`

**Interfaces:**
- Produces Composer mapping `Friendica\Addon\OpenIdConnect\` → `src/`.
- Produces `AddonTestCase::setUp(): void`, which resets the DI test state and request globals before each test.

- [ ] **Step 1: Record the current executable baseline**

Run from `openidconnect/`:

```bash
composer run qa
wc -l openidconnect.php
```

Expected: PHPUnit passes and `openidconnect.php` reports `1784` lines (or the current checked-out total).

- [ ] **Step 2: Write the failing autoload test**

Create `tests/Support/AutoloadTest.php`:

```php
<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use PHPUnit\Framework\TestCase;

final class AutoloadTest extends TestCase
{
    public function testAddonNamespaceIsComposerAutoloadable(): void
    {
        self::assertTrue(class_exists(LoginPolicy::class));
    }
}
```

- [ ] **Step 3: Run the focused test and confirm it fails**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Support/AutoloadTest.php
```

Expected: FAIL because `Friendica\Addon\OpenIdConnect\Auth\LoginPolicy` is not autoloadable.

- [ ] **Step 4: Add the Composer mapping and shared test base**

Add the following key at the root of `composer.json`:

```json
"autoload": {
  "psr-4": {
    "Friendica\\Addon\\OpenIdConnect\\": "src/"
  }
}
```

Create `tests/Support/AddonTestCase.php`:

```php
<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

use Friendica\DI;
use PHPUnit\Framework\TestCase;

abstract class AddonTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DI::resetTestState();
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }
}
```

Change `tests/bootstrap.php` so it requires Composer first, then stubs, then `openidconnect.php`; do not require individual `src` files. Run `composer dump-autoload` and create the minimal `src/Auth/LoginPolicy.php` declaration required by the autoload test:

```php
<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

final class LoginPolicy
{
}
```

- [ ] **Step 5: Migrate the existing test class to the shared base**

Replace its base import and declaration:

```php
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class OpenIdConnectTest extends AddonTestCase
```

Remove the class-local `setUp()` method. Keep every current assertion unchanged in this task.

- [ ] **Step 6: Verify and commit the foundation**

Run:

```bash
composer dump-autoload
composer run qa
```

Expected: PASS.

```bash
git add composer.json tests/bootstrap.php tests/Support/AddonTestCase.php tests/Support/AutoloadTest.php tests/OpenIdConnectTest.php src/Auth/LoginPolicy.php
git commit -m "refactor(openidconnect): add PSR-4 addon boundary"
```

## Phase 1 — Pure Authentication Policy

### Task 2: Move redirect, browser, and PKCE policy into `LoginPolicy`

**Files:**
- Modify: `openidconnect/src/Auth/LoginPolicy.php`
- Create: `openidconnect/tests/Auth/LoginPolicyTest.php`
- Modify: `openidconnect/openidconnect.php`
- Modify: `openidconnect/tests/OpenIdConnectTest.php`

**Interfaces:**
- Produces `LoginPolicy::sanitizeReturnPath(string $returnPath): string`.
- Produces `LoginPolicy::isBearerRequest(array $server): bool`.
- Produces `LoginPolicy::base64UrlEncode(string $value): string`.
- Produces `LoginPolicy::generateState(): string`, `generateNonce(): string`, `generatePkceVerifier(): string`, `generatePkceChallenge(string $verifier): string`.
- Produces `LoginPolicy::shouldAutoRedirect(array $query, array $server, bool $transparentSso): bool` and `buildFallbackPath(string $returnPath): string`.

- [ ] **Step 1: Write focused failing tests**

Create `tests/Auth/LoginPolicyTest.php`:

```php
<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class LoginPolicyTest extends AddonTestCase
{
    public function testSanitizesOnlyRelativeReturnPaths(): void
    {
        self::assertSame('settings/account', LoginPolicy::sanitizeReturnPath('/settings/account'));
        self::assertSame('', LoginPolicy::sanitizeReturnPath('https://evil.example/'));
        self::assertSame('', LoginPolicy::sanitizeReturnPath('mona://oauth'));
    }

    public function testBuildsPkceS256Challenge(): void
    {
        self::assertSame(
            'ungWv48Bz-pBQUDeXa4iI7ADYaOWF3qctBD_YfIAFa0',
            LoginPolicy::generatePkceChallenge('dBjftJeZ4CVP-m1GwdJdC1wV_N9dcVgM2T9JHhSwbLc')
        );
    }

    public function testRejectsBearerAndPostRequestsForAutoRedirect(): void
    {
        self::assertFalse(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'POST'], true));
        self::assertFalse(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'GET', 'HTTP_AUTHORIZATION' => 'Bearer token'], true));
        self::assertTrue(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'GET'], true));
    }
}
```

- [ ] **Step 2: Run the focused test and confirm it fails**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Auth/LoginPolicyTest.php
```

Expected: FAIL because the methods are undefined.

- [ ] **Step 3: Implement the exact pure policy surface**

Replace the placeholder class with methods using the current function bodies from lines 238–388. Preserve constants by moving their values into class constants:

```php
public const STATE_BYTES = 32;
public const NONCE_BYTES = 32;
public const PKCE_VERIFIER_BYTES = 48;
public const NO_AUTO_QUERY_KEY = 'openidconnect_no_auto';
```

`sanitizeReturnPath()` must reject `^(https?:)?//` and `^[a-z][a-z0-9+.-]*:` case-insensitively, then return `ltrim($returnPath, '/')`. `shouldAutoRedirect()` must return false for disabled SSO, non-GET, bearer authorization, or `openidconnect_no_auto=1`; it must otherwise return true.

- [ ] **Step 4: Retain a temporary procedural compatibility layer**

In `openidconnect.php`, replace each extracted function body with a one-line forwarding adapter, for example:

```php
function openidconnect_sanitize_return_path(string $returnPath): string
{
    return LoginPolicy::sanitizeReturnPath($returnPath);
}
```

Do this for all policy/PKCE helpers extracted in this task. Keep these adapters until Task 12 removes them after the existing regression tests have been migrated.

- [ ] **Step 5: Run focused and complete suites**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Auth/LoginPolicyTest.php
composer run qa
```

Expected: both PASS.

- [ ] **Step 6: Commit the policy extraction**

```bash
git add src/Auth/LoginPolicy.php tests/Auth/LoginPolicyTest.php tests/OpenIdConnectTest.php openidconnect.php
git commit -m "refactor(openidconnect): extract login policy"
```

## Phase 2 — Provider Discovery and Token Transport

### Task 3: Extract provider metadata and configuration policy

**Files:**
- Create: `openidconnect/src/Provider/ProviderConfiguration.php`
- Create: `openidconnect/tests/Provider/ProviderConfigurationTest.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Produces `ProviderConfiguration::isConfigured(): bool`.
- Produces `ProviderConfiguration::get(): array`.
- Produces `ProviderConfiguration::clientAuthMethod(array $metadata, string $endpoint = 'token'): string`.
- Uses cache key `openidconnect:provider_config` and `Duration::DAY` unchanged.

- [ ] **Step 1: Write the failing metadata-policy tests**

Create `tests/Provider/ProviderConfigurationTest.php`:

```php
<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class ProviderConfigurationTest extends AddonTestCase
{
    public function testRequiresAllThreeMandatoryConfigurationValues(): void
    {
        self::assertFalse((new ProviderConfiguration())->isConfigured());
    }

    public function testPrefersBasicClientAuthenticationWhenAdvertised(): void
    {
        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod([
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
        ]));
    }

    public function testUsesPostWhenBasicIsNotAdvertised(): void
    {
        self::assertSame('client_secret_post', ProviderConfiguration::clientAuthMethod([
            'token_endpoint_auth_methods_supported' => ['client_secret_post'],
        ]));
    }
}
```

- [ ] **Step 2: Confirm the test fails**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Provider/ProviderConfigurationTest.php
```

Expected: FAIL because `ProviderConfiguration` is absent.

- [ ] **Step 3: Implement metadata handling without changing responses**

Create `ProviderConfiguration` by moving the code from `openidconnect_is_configured`, `openidconnect_get_provider_config`, and `openidconnect_get_client_auth_method`. Keep these exact behaviours: validate discovery URL with `FILTER_VALIDATE_URL`; use `DI::httpClient()->fetch($url, '', 30)`; require non-empty string `authorization_endpoint`; warn but continue for missing `token_endpoint`, `userinfo_endpoint`, and `jwks_uri`; cache only valid parsed metadata for one day; delete malformed cache values.

- [ ] **Step 4: Replace source functions with short forwarding adapters**

Use one shared accessor in the procedural entrypoint:

```php
function openidconnect_provider_configuration(): ProviderConfiguration
{
    static $configuration;
    return $configuration ??= new ProviderConfiguration();
}
```

Make the old three procedural function names delegate to it. This preserves un-migrated callers during subsequent tasks.

- [ ] **Step 5: Verify and commit**

Run:

```bash
composer run qa
```

Expected: PASS.

```bash
git add src/Provider/ProviderConfiguration.php tests/Provider/ProviderConfigurationTest.php openidconnect.php
git commit -m "refactor(openidconnect): isolate provider configuration"
```

### Task 4: Extract token exchange, revocation, and ID-token validation

**Files:**
- Create: `openidconnect/src/Provider/TokenClient.php`
- Create: `openidconnect/src/Provider/IdTokenValidator.php`
- Create: `openidconnect/tests/Provider/IdTokenValidatorTest.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Consumes `ProviderConfiguration::get(): array` and `ProviderConfiguration::clientAuthMethod(): string`.
- Produces `TokenClient::exchangeCode(string $code, string $verifier): array`.
- Produces `TokenClient::revoke(string $endpoint, string $token, array $providerConfig, int $timeout = 30): void`.
- Produces `IdTokenValidator::validate(string $idToken, string $expectedNonce, string $accessToken): object|false`.

- [ ] **Step 1: Write failure tests for deterministic validator helpers**

Create `tests/Provider/IdTokenValidatorTest.php` with the constructor-free data checks that are safe without mocking an HTTP client:

```php
<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class IdTokenValidatorTest extends AddonTestCase
{
    public function testComputesOidcAccessTokenHash(): void
    {
        self::assertSame(
            'ungWv48Bz-pBQUDeXa4iI7ADYaOWF3qctBD_YfIAFa0',
            IdTokenValidator::accessTokenHash('dBjftJeZ4CVP-m1GwdJdC1wV_N9dcVgM2T9JHhSwbLc')
        );
    }

    public function testAcceptsClientIdInScalarOrArrayAudience(): void
    {
        self::assertTrue(IdTokenValidator::hasAudience('client-id', 'client-id'));
        self::assertTrue(IdTokenValidator::hasAudience(['other', 'client-id'], 'client-id'));
        self::assertFalse(IdTokenValidator::hasAudience(['other'], 'client-id'));
    }
}
```

- [ ] **Step 2: Confirm the validator test fails**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Provider/IdTokenValidatorTest.php
```

Expected: FAIL because `IdTokenValidator` does not exist.

- [ ] **Step 3: Implement `TokenClient` exactly from current transport code**

Move lines 821–879 and 1438–1470. `exchangeCode()` must return `[]` for absent endpoint, thrown HTTP error, non-success response, malformed JSON, or missing `access_token`. `revoke()` must remain best-effort and must not throw on an HTTP error. Preserve basic-versus-post credential placement and every timeout.

- [ ] **Step 4: Implement `IdTokenValidator` by preserving every rejection condition**

Move lines 881–1002. Add two public static helpers used by the test:

```php
public static function accessTokenHash(string $accessToken): string
{
    return LoginPolicy::base64UrlEncode(substr(hash('sha256', $accessToken, true), 0, 16));
}

public static function hasAudience(string|array $audience, string $clientId): bool
{
    return in_array($clientId, is_array($audience) ? $audience : [$audience], true);
}
```

`validate()` must preserve exactly one retry after `SignatureInvalidException`, cache key `openidconnect:jwks`, `Duration::DAY`, `JWT::$leeway = 60`, expected issuer derivation, nonce check, multi-audience `azp` check, and optional `at_hash` check. Do not log tokens, JWKS key material, client secret, or authorization code.

- [ ] **Step 5: Add forwarding adapters and verify**

Keep `openidconnect_exchange_code`, `openidconnect_validate_id_token`, and `openidconnect_revoke_token` as one-line delegates. Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Provider/IdTokenValidatorTest.php
composer run qa
```

Expected: PASS.

- [ ] **Step 6: Commit the provider transport extraction**

```bash
git add src/Provider/TokenClient.php src/Provider/IdTokenValidator.php tests/Provider/IdTokenValidatorTest.php openidconnect.php
git commit -m "refactor(openidconnect): separate token services"
```

## Phase 3 — Identity and Local Account Services

### Task 5: Extract userinfo and account-link persistence

**Files:**
- Create: `openidconnect/src/Identity/UserInfo.php`
- Create: `openidconnect/src/Account/AccountLinker.php`
- Create: `openidconnect/tests/Identity/UserInfoTest.php`
- Create: `openidconnect/tests/Account/AccountLinkerTest.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Produces `UserInfo::fetch(string $accessToken): array` and `UserInfo::fromIdToken(object $claims): array`.
- Produces `AccountLinker::get(int $uid): ?array`, `link(int $uid, string $sub, string $email, string $nickname): bool`, and `unlink(int $uid): void`.
- `AccountLinker::link()` returns false for an empty subject or a subject owned by another user.

- [ ] **Step 1: Write tests for claim normalisation and account-link rules**

Create `tests/Identity/UserInfoTest.php`:

```php
<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Identity;

use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class UserInfoTest extends AddonTestCase
{
    public function testMapsClaimsAndUsesNicknameFallback(): void
    {
        $result = (new UserInfo())->fromIdToken((object) ['sub' => 'sub', 'email' => 'u@example.test', 'nickname' => 'nick', 'email_verified' => '1']);
        self::assertSame('nick', $result['preferred_username']);
        self::assertTrue($result['email_verified']);
    }
}
```

Create `tests/Account/AccountLinkerTest.php` with the expected empty-subject contract:

```php
public function testRefusesEmptySubject(): void
{
    self::assertFalse((new AccountLinker())->link(42, '', 'u@example.test', 'user'));
}
```

- [ ] **Step 2: Confirm both focused tests fail**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Identity/UserInfoTest.php tests/Account/AccountLinkerTest.php
```

Expected: FAIL because both classes are absent.

- [ ] **Step 3: Implement `UserInfo`**

Move `openidconnect_extract_userinfo_from_id_token()` and `openidconnect_get_userinfo()` into `UserInfo`. Preserve scalar-only claim coercion, `preferred_username` before `nickname`, optional `email_verified` conversion, bearer header, 30-second timeout, provider endpoint lookup, and empty-array failure contract.

- [ ] **Step 4: Implement `AccountLinker`**

Move `openidconnect_get_linked_account()` and `openidconnect_link_user()` into the service. Implement `unlink()` by moving only the persistence/session removal block from the current unlink route: clear `user.openid`, delete the three `openidconnect` pconfig values, and remove `openidconnect_tokens`. Route-level CSRF, notice, and redirect decisions remain outside this class.

- [ ] **Step 5: Preserve old calls temporarily and verify**

Delegate the three current procedural helpers to the new services. Run:

```bash
composer run qa
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add src/Identity/UserInfo.php src/Account/AccountLinker.php tests/Identity/UserInfoTest.php tests/Account/AccountLinkerTest.php openidconnect.php
git commit -m "refactor(openidconnect): extract identity and account linking"
```

### Task 6: Extract avatar safety and user provisioning

**Files:**
- Create: `openidconnect/src/Account/AvatarUpdater.php`
- Create: `openidconnect/src/Account/UserProvisioner.php`
- Create: `openidconnect/tests/Account/AvatarUpdaterTest.php`
- Create: `openidconnect/tests/Account/UserProvisionerTest.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Produces `AvatarUpdater::isSafeUrl(string $url): bool`, `update(int $uid, string $url): void`, `deleteTemporaryFile(string $path, int $uid): void`.
- Produces `UserProvisioner::normaliseNickname(string $nickname, string $name, string $email): string` and `UserProvisioner::findOrCreate(string $sub, string $email, string $name, string $nickname, string $picture): ?array`.

- [ ] **Step 1: Write regression tests for local-only account rules**

Create `tests/Account/AvatarUpdaterTest.php`:

```php
public function testRejectsNonUrlAndNonHttpsThirdPartyAvatarUrls(): void
{
    self::assertFalse((new AvatarUpdater())->isSafeUrl('not a url'));
    self::assertFalse((new AvatarUpdater())->isSafeUrl('http://cdn.example.test/avatar.png'));
}
```

Create `tests/Account/UserProvisionerTest.php`:

```php
public function testNormalisesNicknameFromNameThenEmailLocalPart(): void
{
    $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());
    self::assertSame('Jane-Doe', $service->normaliseNickname('', 'Jane Doe', 'jane@example.test'));
    self::assertSame('jane', $service->normaliseNickname('', '', 'jane@example.test'));
}
```

- [ ] **Step 2: Confirm tests fail**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Account/AvatarUpdaterTest.php tests/Account/UserProvisionerTest.php
```

Expected: FAIL because the methods/classes are absent.

- [ ] **Step 3: Implement `AvatarUpdater` without relaxing SSRF controls**

Move lines 1222–1347. Preserve same-host-as-discovery exception, HTTPS requirement for third-party hosts, public A/AAAA address check, `avatar_` temporary-file prefix, temp-directory containment check, `finally` cleanup, and `Contact::updateAvatar` call. No remote avatar URL may be fetched before `isSafeUrl()` returns true.

- [ ] **Step 4: Implement `UserProvisioner` without changing account selection**

Move lines 390–406 and 1081–1220. Inject `AccountLinker` and `AvatarUpdater` via constructor. Preserve: lookup by `user.openid`; authoritative IdP email propagation only when unowned; auto-link existing unlinked account only when `auto_create_accounts`; reject email assigned to another subject; `User::create`; unique nickname suffix range 1–9999; explicit `user.openid` update; pconfig writes; and rethrow of user-creation exceptions.

- [ ] **Step 5: Verify and commit**

Run:

```bash
composer run qa
```

Expected: PASS.

```bash
git add src/Account/AvatarUpdater.php src/Account/UserProvisioner.php tests/Account/AvatarUpdaterTest.php tests/Account/UserProvisionerTest.php openidconnect.php
git commit -m "refactor(openidconnect): isolate account provisioning"
```

## Phase 4 — Authorization and Callback Orchestration

### Task 7: Extract authorization-request construction and state persistence

**Files:**
- Create: `openidconnect/src/Auth/AuthorizationRequest.php`
- Create: `openidconnect/tests/Auth/AuthorizationRequestTest.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Consumes `ProviderConfiguration` and `LoginPolicy`.
- Produces `AuthorizationRequest::redirect(bool $linkMode, string $returnPath, bool $promptNone): void`.
- Produces `AuthorizationRequest::consumeState(string $state): array`.
- Uses cache key prefix `oidcstate:` and TTL `600` seconds unchanged.

- [ ] **Step 1: Write state-shape tests before extraction**

Extend the DI cache stub in `tests/stubs/DI.php` only as needed to support `set()`, `get()`, and `delete()`. Create `tests/Auth/AuthorizationRequestTest.php`:

```php
public function testConsumesStateOnlyOnce(): void
{
    DI::cache()->set('oidcstate:state', ['nonce' => 'nonce'], 600);
    $request = new AuthorizationRequest(new ProviderConfiguration());

    self::assertSame(['nonce' => 'nonce'], $request->consumeState('state'));
    self::assertSame([], $request->consumeState('state'));
}
```

- [ ] **Step 2: Confirm failure**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Auth/AuthorizationRequestTest.php
```

Expected: FAIL because the request service is absent.

- [ ] **Step 3: Implement state and authorization redirect flow**

Move lines 408–495. Construct exactly these state arrays:

```php
['return_path' => $returnPath ?: 'settings/account', 'link_mode' => true, 'nonce' => $nonce, 'pkce_verifier' => $pkceVerifier, 'created_at' => time()]
```

for account linking, and:

```php
['return_path' => $returnPath, 'link_mode' => false, 'silent_auth' => $promptNone, 'nonce' => $nonce, 'pkce_verifier' => $pkceVerifier, 'created_at' => time()]
```

for login. Retain `prompt=consent` for link mode and `prompt=none` only for silent login. `consumeState()` must delete cached state after a successful read and return `[]` for missing/failed reads.

- [ ] **Step 4: Add adapter, run tests, commit**

Delegate `openidconnect_redirect_to_provider()` to `AuthorizationRequest::redirect()` and make later code call `consumeState()`. Run `composer run qa`, expect PASS, then:

```bash
git add src/Auth/AuthorizationRequest.php tests/Auth/AuthorizationRequestTest.php tests/stubs/DI.php openidconnect.php
git commit -m "refactor(openidconnect): extract authorization request state"
```

### Task 8: Extract the callback controller without altering authentication decisions

**Files:**
- Create: `openidconnect/src/Auth/CallbackHandler.php`
- Create: `openidconnect/tests/Auth/CallbackHandlerTest.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Consumes `AuthorizationRequest`, `TokenClient`, `IdTokenValidator`, `UserInfo`, `AccountLinker`, `UserProvisioner`, `LoginPolicy`.
- Produces `CallbackHandler::handle(array $query): void`.
- Keeps headers/redirects in this controller because it owns HTTP callback termination.

- [ ] **Step 1: Add characterization tests for callback error decisions**

Add redirect recording to the base URL test stub. Create `tests/Auth/CallbackHandlerTest.php`:

```php
public function testSilentLoginRequiredFallsBackToManualLoginWithReturnPath(): void
{
    DI::cache()->set('oidcstate:s', ['silent_auth' => true, 'return_path' => 'oauth/authorize?client_id=test'], 600);
    (new CallbackHandler($this->dependencies()))->handle(['state' => 's', 'error' => 'login_required']);

    self::assertSame('login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest', DI::baseUrl()->lastRedirect());
}

public function testMissingCodeOrStateRedirectsToLogin(): void
{
    (new CallbackHandler($this->dependencies()))->handle([]);

    self::assertSame('login', DI::baseUrl()->lastRedirect());
}
```

Define `dependencies(): CallbackDependencies` in the test class using the actual service constructors. If the constructor list becomes unwieldy, create `src/Auth/CallbackDependencies.php` with typed readonly properties for the seven collaborators; keep the value object below 100 LOC.

- [ ] **Step 2: Confirm failures**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Auth/CallbackHandlerTest.php
```

Expected: FAIL because `CallbackHandler`/test seam is absent.

- [ ] **Step 3: Transfer the callback in three private methods**

Move lines 497–720 into `CallbackHandler`; split it into:

```php
private function handleAuthorizationError(array $query): bool
private function loadCallbackState(string $state): array
private function verifyIdentity(array $tokens, array $stateData): array
```

Each must keep current notices and redirect targets. `verifyIdentity()` returns `[]` after an already-issued redirect; otherwise it returns normalised userinfo. The public `handle()` then keeps link-mode completion, 2FA session flag, `DI::auth()->setForUser($user, true, true)`, token storage, `session_write_close()`, and final redirect exactly as today.

- [ ] **Step 4: Verify complete callback behaviour and commit**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Auth/CallbackHandlerTest.php
composer run qa
```

Expected: PASS.

```bash
git add src/Auth/CallbackHandler.php src/Auth/CallbackDependencies.php tests/Auth/CallbackHandlerTest.php tests/stubs/DI.php openidconnect.php
git commit -m "refactor(openidconnect): extract OAuth callback handler"
```

## Phase 5 — Route, Session, and Presentation Hooks

### Task 9: Extract lifecycle, routing, account-link routes, and logout/revoke routes into the facade

**Files:**
- Create: `openidconnect/src/OpenIdConnectAddon.php`
- Create: `openidconnect/tests/OpenIdConnectAddonTest.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Produces facade methods `install(): void`, `uninstall(): void`, `loadConfig(ConfigFileManager $loader): void`, `dispatch(string $route): void`, `beginAccountLink(): void`, `unlinkAccount(): void`, `logout(): void`, and `revoke(): void`.
- The procedural entrypoint calls `openidconnect_addon(): OpenIdConnectAddon` and no longer contains business logic.

- [ ] **Step 1: Characterize unknown-route dispatch**

Create `tests/OpenIdConnectAddonTest.php`:

```php
public function testUnknownRouteOnlyLogsWarning(): void
{
    $addon = new OpenIdConnectAddon();
    $addon->dispatch('unknown');

    self::assertSame('openidconnect: unknown route requested', DI::logger()->warnings[0][0]);
}
```

- [ ] **Step 2: Confirm it fails**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/OpenIdConnectAddonTest.php
```

Expected: FAIL because facade class is absent.

- [ ] **Step 3: Implement facade methods by relocating current route code**

Move lifecycle and route logic from lines 43–145, 722–781, and 1393–1504. Preserve hook registration/unregistration arguments, complete uninstall deletion set, state/JWKS cache flush, link preconditions, unlink POST+CSRF check, token/session behaviour, RP-initiated logout parameters, and best-effort access/refresh revocation. `dispatch()` must route exactly `auth`, `callback`, `revoke`, `link`, and `unlink`, and warn for all other values.

- [ ] **Step 4: Shrink procedural functions to facade adapters**

Keep only the Friendica-required global names. Pattern:

```php
function openidconnect_revoke(): void
{
    openidconnect_addon()->revoke();
}
```

Do this for every lifecycle and route function transferred in this task.

- [ ] **Step 5: Verify and commit**

Run:

```bash
composer run qa
```

Expected: PASS.

```bash
git add src/OpenIdConnectAddon.php tests/OpenIdConnectAddonTest.php openidconnect.php
git commit -m "refactor(openidconnect): introduce addon facade"
```

### Task 10: Extract login, settings, moderation, and admin hooks

**Files:**
- Create: `openidconnect/src/Presentation/LoginHook.php`
- Create: `openidconnect/src/Presentation/SettingsHook.php`
- Create: `openidconnect/src/Presentation/ModerationBadgeHook.php`
- Create: `openidconnect/src/Presentation/AdminHook.php`
- Create: `openidconnect/tests/Presentation/AdminHookTest.php`
- Modify: `openidconnect/src/OpenIdConnectAddon.php`
- Modify: `openidconnect/openidconnect.php`

**Interfaces:**
- Produces `LoginHook::append(string &$output): void`.
- Produces `SettingsHook::append(array &$data): void`.
- Produces `ModerationBadgeHook::append(string &$output): void`.
- Produces `AdminHook::render(string &$output): void` and `save(array $post): void`.

- [ ] **Step 1: Write failing admin metadata tests**

Create `tests/Presentation/AdminHookTest.php`:

```php
public function testButtonTextIsEditableEvenWhenItsSourceIsStatic(): void
{
    DI::config()->getCache()->setSource('openidconnect', 'button_text', Cache::SOURCE_STATIC);
    self::assertFalse((new AdminHook())->isReadOnly('button_text'));
}

public function testAllOtherStaticConfigurationIsReadOnly(): void
{
    DI::config()->getCache()->setSource('openidconnect', 'client_id', Cache::SOURCE_STATIC);
    self::assertTrue((new AdminHook())->isReadOnly('client_id'));
}
```

Expand test cache stubs only with `getSource()`/`setSource()` required by this test.

- [ ] **Step 2: Confirm failure**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Presentation/AdminHookTest.php
```

Expected: FAIL because `AdminHook` is absent.

- [ ] **Step 3: Implement hook classes by moving complete existing functions**

Move: login hook from lines 1349–1391; settings hook from 1606–1641; moderation hook from 1506–1599; and admin helpers/render/save from 1643–1784. Preserve template names, stylesheet path, HTML escaping flags, CSRF typename `admin_addons_details`, config source labels, `button_text` edit exception, all admin fields, exact boolean/string key lists, and cache invalidation after save.

- [ ] **Step 4: Delegate facade hook methods and procedural adapters**

`OpenIdConnectAddon` owns instances and delegates. The procedural `openidconnect_sso_initiate`, `openidconnect_page_end`, `openidconnect_addon_settings`, `openidconnect_addon_admin`, and `openidconnect_addon_admin_post` become one-line facade calls.

- [ ] **Step 5: Verify and commit**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml tests/Presentation/AdminHookTest.php
composer run qa
```

Expected: PASS.

```bash
git add src/Presentation tests/Presentation tests/stubs/DI.php src/OpenIdConnectAddon.php openidconnect.php
git commit -m "refactor(openidconnect): modularize presentation hooks"
```

## Phase 6 — Remove Compatibility Layer and Enforce Size Limits

### Task 11: Reduce the procedural entrypoint to only Friendica adapters

**Files:**
- Modify: `openidconnect/openidconnect.php`
- Modify: `openidconnect/tests/bootstrap.php`
- Modify: `openidconnect/tests/OpenIdConnectTest.php`
- Delete: `openidconnect/tests/OpenIdConnectTest.php` after its assertions are moved to focused test files

**Interfaces:**
- Produces only `openidconnect_module`, `openidconnect_init`, lifecycle/hook adapters, and `openidconnect_addon(): OpenIdConnectAddon` in the entrypoint.
- Removes all temporary function aliases for helpers that are no longer Friendica hook/module functions.

- [ ] **Step 1: Move remaining legacy assertions to owning test classes**

Move sanitisation/PKCE/bearer/fallback assertions into `LoginPolicyTest`; client-auth assertions into `ProviderConfigurationTest`; safe URL assertions into `AvatarUpdaterTest`; and ID-token userinfo assertions into `UserInfoTest`. Preserve every old assertion value verbatim.

- [ ] **Step 2: Run the old test once to prove no assertions were dropped**

Run:

```bash
COMPOSER_VENDOR_DIR=vendor-dev vendor-dev/bin/phpunit -c phpunit.xml
```

Expected: PASS before deleting the legacy test file.

- [ ] **Step 3: Remove compatibility helpers**

Delete all global helpers except names called by Friendica: `openidconnect_module`, `openidconnect_init`, `openidconnect_install`, `openidconnect_uninstall`, `openidconnect_load_config`, `openidconnect_sso_initiate`, `openidconnect_logout`, `openidconnect_page_end`, `openidconnect_addon_settings`, `openidconnect_addon_admin`, and `openidconnect_addon_admin_post`.

`openidconnect_init()` retains the existing top-level exception log/notice/`login` redirect and `exit()` behaviour, then calls `openidconnect_addon()->dispatch($route)`.

- [ ] **Step 4: Assert file-size contract**

Run:

```bash
find src -name '*.php' -print0 | xargs -0 wc -l
wc -l openidconnect.php
```

Expected: every individual source file and `openidconnect.php` is below 200 lines. If a file is 200 or more, split it along the boundaries listed in the File Structure table before proceeding; do not weaken the constraint.

- [ ] **Step 5: Run full QA and commit**

Run:

```bash
composer run qa
```

Expected: PASS.

```bash
git add -A openidconnect.php src tests composer.json composer.lock
git commit -m "refactor(openidconnect): remove procedural compatibility layer"
```

## Phase 7 — Full Regression Verification and Documentation

### Task 12: Verify behaviour, repository hygiene, and addon guidance

**Files:**
- Modify: `openidconnect/README.md`
- Modify: `openidconnect/AUTHENTIK_SETUP.md` only if source paths are named
- Modify: `openidconnect/composer.json` only if lint script needs recursive checks

**Interfaces:**
- Produces documented source layout and local validation command.
- Does not change user-facing OIDC configuration instructions.

- [ ] **Step 1: Expand lint coverage to every source file**

Change Composer lint script to:

```json
"lint": "find . -path './vendor' -prune -o -path './vendor-dev' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l"
```

Do not include dependencies in linting.

- [ ] **Step 2: Add a concise developer-maintenance section to README**

Append:

```markdown
## Development layout

`openidconnect.php` is the Friendica hook/module adapter. Addon behaviour lives in focused PSR-4 classes under `src/`:

- `Auth/` handles OAuth browser flow and redirect policy.
- `Provider/` handles discovery, tokens, and JWT validation.
- `Identity/` and `Account/` map provider identity to local users.
- `Presentation/` renders Friendica hooks.

Run `composer run qa` from this directory before submitting changes.
```

- [ ] **Step 3: Run static, test, dependency, and size verification**

Run:

```bash
composer run lint
composer run test
composer run deps:audit
find src -name '*.php' -print0 | xargs -0 wc -l
wc -l openidconnect.php
git diff --check
git status --short
```

Expected: lint/test/audit PASS; all source and entrypoint files below 200 LOC; no whitespace errors; only intended tracked changes.

- [ ] **Step 4: Perform manual Friendica smoke test**

In a disposable Friendica environment configured with a test IdP:

1. Load login page with transparent SSO disabled; confirm button renders and redirects to provider with `state`, `nonce`, and `code_challenge`.
2. Complete login; confirm callback creates or authenticates the intended local account and reaches the original relative return path.
3. Enable `transparent_sso_prompt_none`; verify `login_required` returns to `login?openidconnect_no_auto=1` rather than a redirect loop.
4. Link a logged-in account, then unlink it with the rendered CSRF token; confirm `user.openid`, three pconfig keys, and `openidconnect_tokens` are cleared.
5. Revoke tokens, log out with IdP logout enabled, and confirm the generated endpoint includes `id_token_hint` and `post_logout_redirect_uri`.

- [ ] **Step 5: Commit verification/documentation changes**

```bash
git add README.md AUTHENTIK_SETUP.md composer.json composer.lock
git commit -m "docs(openidconnect): document modular addon layout"
```

## Plan Self-Review

- **Spec coverage:** Tasks 1–2 cover PSR-4, strict source boundaries, and pure policy; Tasks 3–4 cover discovery, OAuth token transport, JWKS, and JWT validation; Tasks 5–6 cover identity mapping, linking, provisioning, and avatars; Tasks 7–8 cover authorization state and callback login; Tasks 9–10 cover routes, lifecycle, logout, and Friendica hooks; Tasks 11–12 remove legacy helpers, enforce the <200 LOC objective, and validate all paths.
- **Placeholder scan:** No deferred work markers are used. Every task names exact paths, contracts, commands, expected results, and required behaviour.
- **Type consistency:** Provider metadata stays `array`; token payload stays `array`; validated identity token stays `object|false`; userinfo stays `array`; linked account stays `?array`; callback and redirect endpoints return `void` because they issue redirect side effects.

## Execution Handoff

Plan complete and saved to `/Users/danieldekay/Dokumente/projects/programmieren/friendica-patches/friendica-addons/.plan/2026-07-13-openidconnect-modularization.md`.

1. **Subagent-Driven (recommended)** — dispatch a fresh subagent per task; review between tasks.
2. **Inline Execution** — execute tasks in this session with checkpoints.

Which approach?