<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

final class TokenClientTest extends DiTestCase
{
    private function seedDiscoveryConfig(array $config = []): void
    {
        $this->cache->set('openidconnect:provider_config', array_replace([
            'authorization_endpoint' => 'https://idp.example/authorize',
            'token_endpoint' => 'https://idp.example/token',
        ], $config));
    }

    public function testExchangeCodeReturnsEmptyArrayWhenProviderHasNoTokenEndpoint(): void
    {
        $this->initDi();
        $this->cache->set('openidconnect:provider_config', ['authorization_endpoint' => 'https://idp.example/authorize']);

        self::assertSame([], (new TokenClient())->exchangeCode('some-code'));
    }

    public function testExchangeCodeUsesBasicAuthWhenAdvertisedByProvider(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $response->method('getBodyString')->willReturn(json_encode(['access_token' => 'tok'], JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::once())->method('post')
            ->with(
                'https://idp.example/token',
                self::isString(),
                self::callback(static fn (array $headers): bool => isset($headers['Authorization']) && str_starts_with($headers['Authorization'], 'Basic ')),
                30
            )
            ->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'client-secret');
        $this->seedDiscoveryConfig(['token_endpoint_auth_methods_supported' => ['client_secret_basic']]);

        $tokens = (new TokenClient())->exchangeCode('some-code');

        self::assertSame(['access_token' => 'tok'], $tokens);
    }

    public function testExchangeCodePutsClientCredentialsInBodyWhenBasicAuthIsNotAdvertised(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $response->method('getBodyString')->willReturn(json_encode(['access_token' => 'tok'], JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::once())->method('post')
            ->with(
                'https://idp.example/token',
                self::callback(static fn (string $body): bool => str_contains($body, 'client_secret=client-secret')),
                self::logicalNot(self::arrayHasKey('Authorization')),
                30
            )
            ->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'client-secret');
        $this->seedDiscoveryConfig(['token_endpoint_auth_methods_supported' => ['client_secret_post']]);

        (new TokenClient())->exchangeCode('some-code');
    }

    public function testExchangeCodeIncludesCodeVerifierWhenProvided(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $response->method('getBodyString')->willReturn(json_encode(['access_token' => 'tok'], JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::once())->method('post')
            ->with(self::anything(), self::callback(static fn (string $body): bool => str_contains($body, 'code_verifier=verifier-value')), self::anything(), self::anything())
            ->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->seedDiscoveryConfig();

        (new TokenClient())->exchangeCode('some-code', 'verifier-value');
    }

    public function testExchangeCodeReturnsEmptyArrayWhenResponseIsNotSuccessful(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(false);
        $response->method('getReturnCode')->willReturn('400');
        $response->method('getBodyString')->willReturn('{"error":"invalid_grant"}');

        $httpClient = $this->mockHttpClient();
        $httpClient->method('post')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->seedDiscoveryConfig();

        self::assertSame([], (new TokenClient())->exchangeCode('some-code'));
    }

    public function testExchangeCodeReturnsEmptyArrayWhenAccessTokenIsMissing(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $response->method('getBodyString')->willReturn(json_encode(['token_type' => 'Bearer'], JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->method('post')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->seedDiscoveryConfig();

        self::assertSame([], (new TokenClient())->exchangeCode('some-code'));
    }

    public function testExchangeCodeReturnsEmptyArrayWhenHttpClientThrows(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->method('post')->willThrowException(new \RuntimeException('connection reset'));
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->seedDiscoveryConfig();

        self::assertSame([], (new TokenClient())->exchangeCode('some-code'));
    }

    public function testRevokeSendsTokenToProvidedEndpoint(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);

        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::once())->method('post')
            ->with('https://idp.example/revoke', self::isString(), self::anything(), 15)
            ->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'client-secret');

        (new TokenClient())->revoke('https://idp.example/revoke', 'refresh-token', [], 15);
    }

    public function testRevokeSwallowsHttpClientExceptions(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->method('post')->willThrowException(new \RuntimeException('timeout'));
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);

        (new TokenClient())->revoke('https://idp.example/revoke', 'refresh-token', []);

        self::assertTrue($this->logger->hasRecordMatching('warning', 'revocation endpoint request threw exception'));
    }

    public function testConstructorAcceptsInjectedProviderConfiguration(): void
    {
        $this->initDi();
        $this->cache->set('openidconnect:provider_config', ['authorization_endpoint' => 'https://idp.example/authorize']);

        self::assertSame([], (new TokenClient(new ProviderConfiguration()))->exchangeCode('some-code'));
    }
}
