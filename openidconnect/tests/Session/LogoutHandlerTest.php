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
}
