<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Route;

use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
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

    public function testHandleClearsSessionLocallyWhenProviderHasNoRevocationEndpoint(): void
    {
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['form_security_token'] = 'test-token-openidconnect_revoke';

        (new RevokeRoute())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertSame('', DI::baseUrl()->lastRedirect());
        self::assertCount(0, DI::httpClient()->postCalls);
    }

    public function testHandleRedirectsWithoutRevokingWhenAccessTokenIsMissing(): void
    {
        DI::session()->set('openidconnect_tokens', [
            'refresh_token' => 'refresh-token',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['form_security_token'] = 'test-token-openidconnect_revoke';

        (new RevokeRoute())->handle();

        self::assertSame('refresh-token', DI::session()->get('openidconnect_tokens')['refresh_token']);
        self::assertSame('', DI::baseUrl()->lastRedirect());
        self::assertCount(0, DI::httpClient()->postCalls);
    }

    public function testHandleRevokesOnlyAccessTokenWhenRefreshTokenIsAbsent(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://id.example/revoke',
            'revocation_endpoint_auth_methods_supported' => ['client_secret_post'],
        ], 600);
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['form_security_token'] = 'test-token-openidconnect_revoke';

        (new RevokeRoute())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertSame('', DI::baseUrl()->lastRedirect());
        self::assertCount(1, DI::httpClient()->postCalls);
        self::assertStringContainsString('token=access-token', (string) DI::httpClient()->postCalls[0]['postData']);
    }

    public function testHandleLogsWarningsWhenInjectedTokenClientThrows(): void
    {
        $throwingClient = new class extends TokenClient {
            public function revoke(string $endpoint, string $token, array $providerConfig, int $timeout = 30): void
            {
                throw new \RuntimeException('forced revoke failure');
            }
        };

        DI::cache()->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://id.example/revoke',
        ], 600);
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['form_security_token'] = 'test-token-openidconnect_revoke';

        (new RevokeRoute(null, $throwingClient))->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertSame('', DI::baseUrl()->lastRedirect());
        self::assertCount(2, DI::logger()->warnings);
        self::assertSame('openidconnect: access token revocation failed', DI::logger()->warnings[0][0]);
        self::assertSame('RuntimeException', DI::logger()->warnings[0][1]['exception'] ?? null);
        self::assertSame('openidconnect: refresh token revocation failed', DI::logger()->warnings[1][0]);
        self::assertSame('RuntimeException', DI::logger()->warnings[1][1]['exception'] ?? null);
    }

    public function testHandleLogsSafeWarningsAndClearsSessionWhenRevocationThrows(): void
    {
        DI::session()->set('openidconnect_tokens', [
            'access_token' => 'access-token-123',
            'refresh_token' => 'refresh-token-456',
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['form_security_token'] = 'test-token-openidconnect_revoke';

        DI::cache()->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://id.example/revoke',
        ], 600);

        DI::httpClient()->nextException = new \RuntimeException('failed for access-token-123 with super-secret');

        (new RevokeRoute())->handle();

        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertCount(2, DI::logger()->warnings);

        foreach (DI::logger()->warnings as [$message, $context]) {
            self::assertContains($message, [
                'openidconnect: revocation endpoint request threw exception',
                'openidconnect: revocation endpoint returned non-success',
            ]);
            $contextEncoded = json_encode($context, JSON_THROW_ON_ERROR);
            self::assertStringNotContainsString('access-token-123', $contextEncoded);
            self::assertStringNotContainsString('refresh-token-456', $contextEncoded);
            self::assertStringNotContainsString('super-secret', $contextEncoded);
        }
    }
}
