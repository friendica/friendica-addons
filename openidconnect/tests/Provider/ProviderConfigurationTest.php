<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\TestHttpResponse;
use Friendica\DI;

final class ProviderConfigurationTest extends AddonTestCase
{
    public function testRequiresAllThreeMandatoryConfigurationValues(): void
    {
        self::assertFalse((new ProviderConfiguration())->isConfigured());
    }

    public function testPrefersBasicClientAuthenticationWhenAdvertised(): void
    {
        $config = ['token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic']];

        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod($config));
    }

    public function testUsesPostWhenBasicIsNotAdvertised(): void
    {
        $config = ['token_endpoint_auth_methods_supported' => ['client_secret_post']];

        self::assertSame('client_secret_post', ProviderConfiguration::clientAuthMethod($config));
    }

    public function testDefaultsToBasicWhenMetadataAuthMethodsTypeIsInvalid(): void
    {
        $config = ['token_endpoint_auth_methods_supported' => 'client_secret_post'];

        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod($config));
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame(
            'openidconnect: provider metadata auth methods has invalid type, defaulting to client_secret_basic',
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]
        );
    }

    public function testGetPurgesInvalidCachedTypeBeforeRefetchingDiscoveryDocument(): void
    {
        DI::cache()->set('openidconnect:provider_config', 'corrupt-cache', 600);
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode(['authorization_endpoint' => 'https://id.example/authorize'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://id.example/authorize', $result['authorization_endpoint']);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertContains(
            'openidconnect: provider config cache contained invalid type',
            array_column(DI::logger()->warnings, 0)
        );
    }

    public function testGetLogsStatusAndBodySnippetWhenDiscoveryRequestFails(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(false, 'temporarily unavailable', 503);

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame('openidconnect: failed to fetch OIDC discovery document', DI::logger()->errors[array_key_last(DI::logger()->errors)][0]);
        self::assertSame(503, DI::logger()->errors[array_key_last(DI::logger()->errors)][1]['code']);
        self::assertSame('temporarily unavailable', DI::logger()->errors[array_key_last(DI::logger()->errors)][1]['body']);
    }

    public function testGetFailureLogRedactsSensitiveFieldsFromDiscoveryBodySnippet(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            false,
            '{"token":"access-token-123","client_secret":"super-secret","email":"person@example.test","sub":"subject-123"}',
            503
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        self::assertSame('openidconnect: failed to fetch OIDC discovery document', $lastError[0]);
        $contextEncoded = json_encode($lastError[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token-123', $contextEncoded);
        self::assertStringNotContainsString('super-secret', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
        self::assertStringNotContainsString('subject-123', $contextEncoded);
    }

    public function testGetUsesCacheAfterSuccessfulDiscoveryFetch(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode(['authorization_endpoint' => 'https://id.example/authorize'], JSON_THROW_ON_ERROR),
            200
        );

        $provider = new ProviderConfiguration();
        $first = $provider->get();
        $second = $provider->get();

        self::assertSame('https://id.example/authorize', $first['authorization_endpoint']);
        self::assertSame($first, $second);
        self::assertCount(1, DI::httpClient()->getCalls);
    }

    public function testGetReturnsConfigWhenCacheWriteFailsAfterSuccessfulDiscovery(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode(['authorization_endpoint' => 'https://id.example/authorize'], JSON_THROW_ON_ERROR),
            200
        );
        DI::cache()->nextSetException = new \RuntimeException('cache write failed');

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://id.example/authorize', $result['authorization_endpoint']);
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame(
            'openidconnect: failed to cache OIDC discovery document',
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]
        );
    }
}
