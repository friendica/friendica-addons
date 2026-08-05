<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Session;

use Friendica\Addon\OpenIdConnect\Session\CookieSpy;
use Friendica\Addon\OpenIdConnect\Session\LogoutHandler;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class LogoutHandlerTest extends AddonTestCase
{
    public function testHandleClearsOidcTokensAndSetsLogoutCookieWithoutIdToken(): void
    {
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token',
        ]);

        (new LogoutHandler())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertCount(1, CookieSpy::$calls);
        self::assertSame(OIDC_LOGOUT_NO_AUTO_COOKIE, CookieSpy::$calls[0]['name']);
        self::assertSame('1', CookieSpy::$calls[0]['value']);
        self::assertTrue((bool)CookieSpy::$calls[0]['options']['secure']);
        self::assertTrue((bool)CookieSpy::$calls[0]['options']['httponly']);
        self::assertSame('/', CookieSpy::$calls[0]['options']['path']);
    }

    public function testHandleRevokesAccessTokenWhenRevocationEndpointExists(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::session()->set('openidconnect_tokens', [
            'id_token' => 'id-token',
            'access_token' => 'access-token',
        ]);
        DI::session()->set('keep_me', 'still-here');
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
            'revocation_endpoint' => 'https://id.example/revoke',
            'end_session_endpoint' => 'https://id.example/logout',
        ], 600);

        (new LogoutHandler())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertSame('still-here', DI::session()->get('keep_me'));
        self::assertCount(1, DI::httpClient()->postCalls);
        self::assertSame('https://id.example/revoke', DI::httpClient()->postCalls[0]['url']);
    }

    public function testHandleSkipsRevocationWhenNoRevocationEndpointExists(): void
    {
        DI::session()->set('openidconnect_tokens', [
            'id_token' => 'id-token',
            'access_token' => 'access-token',
        ]);
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
            'end_session_endpoint' => '',
        ], 600);

        (new LogoutHandler())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertCount(0, DI::httpClient()->postCalls);
    }

    public function testHandleRevocationFailureLogDoesNotLeakTokenOrSecretLikeValues(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'super-sensitive-client-secret');
        DI::session()->set('openidconnect_tokens', [
            'id_token' => 'id-token-123',
            'access_token' => 'access-token-123',
        ]);
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
            'revocation_endpoint' => 'https://id.example/revoke',
            'end_session_endpoint' => 'https://id.example/logout',
        ], 600);
        DI::httpClient()->nextException = new \RuntimeException('timeout while revoking access-token-123 using super-sensitive-client-secret');

        (new LogoutHandler())->handle();

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: revocation endpoint request threw exception', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token-123', $contextEncoded);
        self::assertStringNotContainsString('super-sensitive-client-secret', $contextEncoded);
    }

    public function testHandleFallsBackToLocalLogoutWhenIdpSignoutDisabled(): void
    {
        DI::config()->set('openidconnect', 'idp_signout', false);
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::session()->set('openidconnect_tokens', [
            'id_token' => 'id-token',
            'access_token' => 'access-token',
        ]);
        DI::session()->set('keep_me', 'still-here');
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
            'revocation_endpoint' => 'https://id.example/revoke',
            'end_session_endpoint' => 'https://id.example/logout',
        ], 600);

        (new LogoutHandler())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertSame('still-here', DI::session()->get('keep_me'));
        self::assertCount(1, DI::httpClient()->postCalls);
        self::assertCount(1, CookieSpy::$calls);
    }

    public function testHandleLogsSafeWarningWhenInjectedRevocationClientThrows(): void
    {
        DI::config()->set('openidconnect', 'idp_signout', false);
        DI::session()->set('openidconnect_tokens', [
            'id_token' => 'id-token-123',
            'access_token' => 'access-token-123',
        ]);
        DI::session()->set('keep_me', 'still-here');

        DI::cache()->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://id.example/revoke',
            'end_session_endpoint' => 'https://id.example/logout',
        ], 600);

        DI::httpClient()->nextException = new \RuntimeException('failed for access-token-123 using client-secret');

        (new LogoutHandler())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertSame('still-here', DI::session()->get('keep_me'));
        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: revocation endpoint request threw exception', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token-123', $contextEncoded);
        self::assertStringNotContainsString('client-secret', $contextEncoded);
    }
}
