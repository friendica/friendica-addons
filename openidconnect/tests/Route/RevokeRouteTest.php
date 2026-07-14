<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Route;

use Friendica\Addon\OpenIdConnect\Route\RevokeRoute;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class RevokeRouteTest extends AddonTestCase
{
    public function testHandleRejectsGetRequestsWithoutRevokingTokens(): void
    {
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'GET';

        (new RevokeRoute())->handle();

        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertSame('', DI::baseUrl()->lastRedirect());
    }

    public function testHandleRejectsPostWithoutCsrfToken(): void
    {
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        (new RevokeRoute())->handle();

        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertSame('', DI::baseUrl()->lastRedirect());
    }

    public function testHandleRevokesAccessAndRefreshTokensThenClearsSession(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://id.example/revoke',
            'revocation_endpoint_auth_methods_supported' => ['client_secret_post'],
        ], 600);
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['form_security_token'] = 'test-token-openidconnect_revoke';

        (new RevokeRoute())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertSame('', DI::baseUrl()->lastRedirect());
        self::assertCount(2, DI::httpClient()->postCalls);
        self::assertStringContainsString('token=access-token', (string) DI::httpClient()->postCalls[0]['postData']);
        self::assertStringContainsString('token=refresh-token', (string) DI::httpClient()->postCalls[1]['postData']);
    }
}
