<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Session;

use Friendica\Addon\OpenIdConnect\Session\LogoutHandler;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

final class LogoutHandlerTest extends DiTestCase
{
    public function testHandleClearsOidcTokensWithoutIdToken(): void
    {
        $this->initDi();
        $this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);

        (new LogoutHandler())->handle();

        self::assertNull($this->session->get('openidconnect_tokens'));
    }

    public function testHandleRevokesAccessTokenWhenRevocationEndpointExists(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::once())->method('post')->with('https://idp.example/revoke')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'client-secret');
        $this->session->set('openidconnect_tokens', ['id_token' => 'id-token', 'access_token' => 'access-token']);
        $this->session->set('keep_me', 'still-here');
        $this->cache->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://idp.example/revoke',
            'end_session_endpoint' => 'https://idp.example/logout',
        ]);
        $this->config->set('openidconnect', 'idp_signout', false);

        (new LogoutHandler())->handle();

        self::assertNull($this->session->get('openidconnect_tokens'));
        self::assertSame('still-here', $this->session->get('keep_me'));
    }

    public function testHandleSkipsRevocationWhenNoRevocationEndpointExists(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::never())->method('post');
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->session->set('openidconnect_tokens', ['id_token' => 'id-token', 'access_token' => 'access-token']);
        $this->cache->set('openidconnect:provider_config', ['end_session_endpoint' => '']);

        (new LogoutHandler())->handle();

        self::assertNull($this->session->get('openidconnect_tokens'));
    }

    public function testHandleLogsSafeWarningWhenRevocationThrows(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->method('post')->willThrowException(new \RuntimeException('timeout while revoking access-token-123 using super-sensitive-client-secret'));
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'super-sensitive-client-secret');
        $this->session->set('openidconnect_tokens', ['id_token' => 'id-token-123', 'access_token' => 'access-token-123']);
        $this->cache->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://idp.example/revoke',
            'end_session_endpoint' => 'https://idp.example/logout',
        ]);
        $this->config->set('openidconnect', 'idp_signout', false);

        (new LogoutHandler())->handle();

        self::assertTrue($this->logger->hasRecordMatching('warning', 'openidconnect: revocation endpoint request threw exception'));
        foreach ($this->logger->records as $record) {
            $encoded = json_encode($record['context'], JSON_THROW_ON_ERROR);
            self::assertStringNotContainsString('access-token-123', $encoded);
            self::assertStringNotContainsString('super-sensitive-client-secret', $encoded);
        }
    }

    public function testHandleFallsBackToLocalLogoutWhenIdpSignoutDisabled(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::once())->method('post')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'idp_signout', false);
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'client-secret');
        $this->session->set('openidconnect_tokens', ['id_token' => 'id-token', 'access_token' => 'access-token']);
        $this->session->set('keep_me', 'still-here');
        $this->cache->set('openidconnect:provider_config', [
            'revocation_endpoint' => 'https://idp.example/revoke',
            'end_session_endpoint' => 'https://idp.example/logout',
        ]);

        (new LogoutHandler())->handle();

        self::assertNull($this->session->get('openidconnect_tokens'));
        self::assertSame('still-here', $this->session->get('keep_me'));
    }
}
