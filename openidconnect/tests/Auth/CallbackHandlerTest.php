<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\TestHttpResponse;
use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Auth\CallbackDependencies;
use Friendica\Addon\OpenIdConnect\Auth\CallbackHandler;
use Friendica\Addon\OpenIdConnect\Auth\CallbackIdentityVerifier;
use Friendica\Addon\OpenIdConnect\Auth\CallbackLinkCompleter;
use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Auth\SessionFunctionSpy;
use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Database\DBA;
use Friendica\DI;

final class CallbackHandlerTest extends AddonTestCase
{
    public function testSilentLoginRequiredFallsBackToManualLoginWithReturnPath(): void
    {
        DI::cache()->set('oidcstate:s', ['silent_auth' => true, 'return_path' => 'oauth/authorize?client_id=test'], 600);
        (new CallbackHandler($this->dependencies()))->handle(['state' => 's', 'error' => 'login_required']);

        self::assertSame('login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest', DI::baseUrl()->lastRedirect());
    }

    public function testAuthorizationErrorLogContextDoesNotLeakRawStateValue(): void
    {
        DI::cache()->set('oidcstate:opaque-secret-state', ['silent_auth' => false], 600);

        (new CallbackHandler($this->dependencies()))->handle([
            'state' => 'opaque-secret-state',
            'error' => 'access_denied',
        ]);

        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: authorization endpoint returned an error', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('opaque-secret-state', $contextEncoded);
    }

    public function testMissingCodeOrStateRedirectsToLogin(): void
    {
        (new CallbackHandler($this->dependencies()))->handle([]);

        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
    }

    public function testExpiredStateRedirectsToLoginWithNotice(): void
    {
        (new CallbackHandler($this->dependencies()))->handle([
            'code' => 'auth-code',
            'state' => 'missing-state',
        ]);

        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString(
            'invalid or expired state',
            implode(' ', DI::sysmsg()->notices)
        );
    }

    public function testLinkModeCallbackStoresTokensClosesSessionAndRedirectsToReturnPath(): void
    {
        DI::userSession()->setLocalUserId(7);
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
            'userinfo_endpoint' => 'https://id.example/userinfo',
            'jwks_uri' => 'https://id.example/jwks',
        ], 600);

        DI::cache()->set('oidcstate:s', [
            'nonce' => 'nonce',
            'pkce_verifier' => 'pkce',
            'return_path' => 'settings/account',
            'link_mode' => true,
        ], 600);

        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'access-token'], JSON_THROW_ON_ERROR),
            200
        );
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'person@example.test',
                'name' => 'Person Example',
                'preferred_username' => 'person',
            ], JSON_THROW_ON_ERROR),
            200
        );

        (new CallbackHandler($this->dependencies()))->handle([
            'code' => 'auth-code',
            'state' => 's',
        ]);

        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertTrue(SessionFunctionSpy::$sessionWriteCloseCalled);
        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
    }

    public function testEmptyIdentityVerificationFallsBackToManualLoginWithNotice(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
            'jwks_uri' => 'https://id.example/jwks',
        ], 600);

        DI::cache()->set('oidcstate:s', [
            'nonce' => 'nonce',
            'return_path' => 'oauth/authorize?client_id=test',
        ], 600);

        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'token'], JSON_THROW_ON_ERROR),
            200
        );

        (new CallbackHandler($this->dependencies()))->handle([
            'code' => 'auth-code',
            'state' => 's',
        ]);

        self::assertSame(
            'login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest',
            DI::baseUrl()->lastRedirect()
        );
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString(
            'could not verify your identity',
            implode(' ', DI::sysmsg()->notices)
        );
    }

    public function testSuccessfulNonLinkCallbackAuthenticatesUserStoresTokensAndSanitizesExternalReturnPath(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
            'userinfo_endpoint' => 'https://id.example/userinfo',
            'jwks_uri' => 'https://id.example/jwks',
        ], 600);
        DI::cache()->set('oidcstate:s', [
            'nonce' => 'nonce',
            'pkce_verifier' => 'pkce',
            'return_path' => 'https://evil.example/phish',
        ], 600);
        DBA::seedUser([
            'uid' => 11,
            'openid' => 'sub-1',
            'email' => 'person@example.test',
            'nickname' => 'person',
        ]);

        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'access-token'], JSON_THROW_ON_ERROR),
            200
        );
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'person@example.test',
                'name' => 'Person Example',
                'preferred_username' => 'person',
            ], JSON_THROW_ON_ERROR),
            200
        );

        (new CallbackHandler($this->dependencies()))->handle([
            'code' => 'auth-code',
            'state' => 's',
        ]);

        self::assertSame(11, DI::auth()->authenticatedUser['uid'] ?? null);
        self::assertTrue((bool) DI::session()->get('2fa'));
        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertTrue(SessionFunctionSpy::$sessionWriteCloseCalled);
        self::assertSame('', DI::baseUrl()->lastRedirect());
        self::assertSame([], DI::sysmsg()->notices);

        $logPayload = json_encode([
            'warnings' => DI::logger()->warnings,
            'errors' => DI::logger()->errors,
            'debugs' => DI::logger()->debugs,
        ], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token', $logPayload);
        self::assertStringNotContainsString('person@example.test', $logPayload);
        self::assertStringNotContainsString('sub-1', $logPayload);
    }

    public function testAuthorizationErrorWithAbsoluteReturnPathFallsBackToLoginWithoutLeakingPath(): void
    {
        DI::cache()->set('oidcstate:s', [
            'silent_auth' => false,
            'return_path' => 'https://evil.example/phish?token=secret',
        ], 600);

        (new CallbackHandler($this->dependencies()))->handle([
            'state' => 's',
            'error' => 'access_denied',
        ]);

        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertStringNotContainsString('evil.example', implode(' ', DI::sysmsg()->notices));
    }

    private function dependencies(): CallbackDependencies
    {
        return new CallbackDependencies(
            new AuthorizationRequest(new ProviderConfiguration()),
            new TokenClient(),
            new CallbackIdentityVerifier(new IdTokenValidator(), new UserInfo(new ProviderConfiguration())),
            new CallbackLinkCompleter(new AccountLinker()),
            new UserProvisioner(new AccountLinker(), new AvatarUpdater()),
        );
    }
}
