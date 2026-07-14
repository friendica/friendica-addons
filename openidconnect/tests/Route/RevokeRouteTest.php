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
}
