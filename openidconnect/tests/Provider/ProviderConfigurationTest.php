<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

final class ProviderConfigurationTest extends DiTestCase
{
    public function testIsConfiguredIsFalseWhenAnyRequiredKeyIsMissing(): void
    {
        $this->initDi();
        $this->config->set('openidconnect', 'client_id', 'abc');
        $this->config->set('openidconnect', 'client_secret', 'secret');

        self::assertFalse((new ProviderConfiguration())->isConfigured());
    }

    public function testIsConfiguredIsTrueWhenAllRequiredKeysArePresent(): void
    {
        $this->initDi();
        $this->config->set('openidconnect', 'client_id', 'abc');
        $this->config->set('openidconnect', 'client_secret', 'secret');
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        self::assertTrue((new ProviderConfiguration())->isConfigured());
    }

    public function testGetReturnsEmptyArrayWhenDiscoveryUrlIsMissing(): void
    {
        $this->initDi();

        self::assertSame([], (new ProviderConfiguration())->get());
    }

    public function testGetReturnsEmptyArrayWhenDiscoveryUrlIsInvalid(): void
    {
        $this->initDi();
        $this->config->set('openidconnect', 'discovery_url', 'not-a-url');

        self::assertSame([], (new ProviderConfiguration())->get());
    }

    public function testGetReturnsCachedConfigWithoutCallingHttpClient(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::never())->method('get');
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');
        $this->cache->set('openidconnect:provider_config', ['authorization_endpoint' => 'https://idp.example/authorize']);

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://idp.example/authorize', $result['authorization_endpoint']);
    }

    public function testGetFetchesAndCachesDiscoveryDocumentOnCacheMiss(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $response->method('getBodyString')->willReturn(json_encode([
            'authorization_endpoint' => 'https://idp.example/authorize',
            'token_endpoint' => 'https://idp.example/token',
            'userinfo_endpoint' => 'https://idp.example/userinfo',
            'jwks_uri' => 'https://idp.example/jwks',
            'issuer' => 'https://idp.example',
        ], JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::once())->method('get')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://idp.example/authorize', $result['authorization_endpoint']);
        self::assertSame($result, $this->cache->get('openidconnect:provider_config'));
    }

    public function testGetReturnsEmptyArrayWhenDiscoveryDocumentIsMissingAuthorizationEndpoint(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(true);
        $response->method('getBodyString')->willReturn(json_encode(['token_endpoint' => 'https://idp.example/token'], JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->method('get')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        self::assertSame([], (new ProviderConfiguration())->get());
    }

    public function testGetReturnsEmptyArrayWhenHttpRequestFails(): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn(false);
        $response->method('getReturnCode')->willReturn('500');
        $response->method('getBodyString')->willReturn('server error');

        $httpClient = $this->mockHttpClient();
        $httpClient->method('get')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        self::assertSame([], (new ProviderConfiguration())->get());
    }

    public function testClientAuthMethodDefaultsToBasicWhenNotAdvertised(): void
    {
        $this->initDi();

        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod([]));
    }

    public function testClientAuthMethodPrefersBasicWhenBothAreAdvertised(): void
    {
        $this->initDi();

        $metadata = ['token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic']];

        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod($metadata));
    }

    public function testClientAuthMethodFallsBackToPostWhenBasicIsNotAdvertised(): void
    {
        $this->initDi();

        $metadata = ['token_endpoint_auth_methods_supported' => ['client_secret_post']];

        self::assertSame('client_secret_post', ProviderConfiguration::clientAuthMethod($metadata));
    }

    public function testClientAuthMethodUsesRevocationEndpointMethodsForRevocation(): void
    {
        $this->initDi();

        $metadata = ['revocation_endpoint_auth_methods_supported' => ['client_secret_post']];

        self::assertSame('client_secret_post', ProviderConfiguration::clientAuthMethod($metadata, 'revocation'));
    }
}
