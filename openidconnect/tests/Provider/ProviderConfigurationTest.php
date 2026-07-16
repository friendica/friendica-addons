<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\TestHttpResponse;
use Friendica\DI;

final class ProviderConfigurationTest extends AddonTestCase
{
    // --- isConfigured ---

    public function testRequiresAllThreeMandatoryConfigurationValues(): void
    {
        self::assertFalse((new ProviderConfiguration())->isConfigured());
    }

    public function testIsConfiguredReturnsFalseForEmptyNonStringRequiredValue(): void
    {
        DI::config()->set('openidconnect', 'client_id', 1);
        DI::config()->set('openidconnect', 'client_secret', 0);
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        self::assertFalse((new ProviderConfiguration())->isConfigured());
    }

    public function testIsConfiguredAcceptsNonStringNonEmptyRequiredValues(): void
    {
        DI::config()->set('openidconnect', 'client_id', 1);
        DI::config()->set('openidconnect', 'client_secret', 2);
        DI::config()->set('openidconnect', 'discovery_url', 3);

        self::assertTrue((new ProviderConfiguration())->isConfigured());
    }

    public function testIsConfiguredReturnsFalseForWhitespaceOnlyStringValue(): void
    {
        DI::config()->set('openidconnect', 'client_id', " \n\t ");
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        self::assertFalse((new ProviderConfiguration())->isConfigured());
    }

    public function testIsConfiguredReturnsTrueWhenAllRequiredStringsAreNonEmpty(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        self::assertTrue((new ProviderConfiguration())->isConfigured());
    }

    // --- clientAuthMethod ---

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

    public function testClientAuthMethodUsesRevocationMetadataKeyWhenEndpointIsRevocation(): void
    {
        $config = [
            'token_endpoint_auth_methods_supported' => ['client_secret_post'],
            'revocation_endpoint_auth_methods_supported' => ['client_secret_basic'],
        ];

        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod($config, 'revocation'));
    }

    public function testClientAuthMethodDefaultsToBasicWhenAuthMethodsArrayIsEmpty(): void
    {
        $config = ['token_endpoint_auth_methods_supported' => []];

        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod($config));
    }

    // --- get ---

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

    public function testGetReturnsImmediatelyWhenValidConfigIsInCache(): void
    {
        $cached = [
            'authorization_endpoint' => 'https://id.example/authorize',
            'token_endpoint' => 'https://id.example/token',
            'userinfo_endpoint' => 'https://id.example/userinfo',
            'jwks_uri' => 'https://id.example/jwks',
            'issuer' => 'https://id.example',
        ];
        DI::cache()->set('openidconnect:provider_config', $cached, 3600);

        $result = (new ProviderConfiguration())->get();

        self::assertSame($cached, $result);
        self::assertSame([], DI::httpClient()->getCalls);
    }

    public function testGetReturnsEmptyAndLogsErrorWhenDiscoveryUrlIsEmptyString(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', '');

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertSame([], DI::httpClient()->getCalls);
        self::assertSame('openidconnect: discovery_url is empty', DI::logger()->errors[array_key_last(DI::logger()->errors)][0]);
    }

    public function testGetReturnsEmptyAndLogsErrorWhenDiscoveryUrlIsInvalid(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'not-a-url');

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertSame([], DI::httpClient()->getCalls);
        self::assertSame('openidconnect: discovery_url is invalid', DI::logger()->errors[array_key_last(DI::logger()->errors)][0]);
    }

    public function testGetAllowsInsecureHttpDiscoveryUrlWhenOtherwiseValid(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'http://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'authorization_endpoint' => 'http://id.example/authorize',
                'token_endpoint' => 'http://id.example/token',
                'userinfo_endpoint' => 'http://id.example/userinfo',
                'jwks_uri' => 'http://id.example/jwks',
                'issuer' => 'http://id.example',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame('http://id.example/authorize', $result['authorization_endpoint']);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertSame(
            'http://id.example/.well-known/openid-configuration',
            DI::httpClient()->getCalls[0]['url']
        );
    }

    public function testGetUsesHttpClientOptionsTimeoutConstantWhenClassExists(): void
    {
        if (!class_exists(\Friendica\Network\HTTPClient\Client\HttpClientOptions::class)) {
            eval('namespace Friendica\\Network\\HTTPClient\\Client; final class HttpClientOptions { public const TIMEOUT = "timeout"; }');
        }

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode(['authorization_endpoint' => 'https://id.example/authorize'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://id.example/authorize', $result['authorization_endpoint']);
        self::assertArrayHasKey('timeout', DI::httpClient()->getCalls[0]['options']);
        self::assertSame(30, DI::httpClient()->getCalls[0]['options']['timeout']);
    }

    public function testGetReturnsEmptyAndLogsErrorWhenHttpClientThrows(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextException = new \RuntimeException('network down');

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertSame(
            'openidconnect: exception while fetching discovery document',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testGetReturnsEmptyAndLogsErrorWhenDiscoveryBodyIsEmptyString(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(true, '', 200);

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertSame(
            'openidconnect: empty OIDC discovery response body',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testGetReturnsEmptyAndLogsErrorWhenDiscoveryJsonIsMalformed(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(true, '{"authorization_endpoint":', 200);

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertSame(
            'openidconnect: malformed JSON in OIDC discovery document',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testGetReturnsEmptyAndLogsErrorWhenAuthorizationEndpointIsMissingOrInvalid(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode(['authorization_endpoint' => 123], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        self::assertSame(
            'openidconnect: invalid OIDC discovery document, missing authorization_endpoint',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testGetLogsWarningWhenTokenEndpointMissingButReturnsConfig(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'authorization_endpoint' => 'https://id.example/authorize',
                'userinfo_endpoint' => 'https://id.example/userinfo',
                'jwks_uri' => 'https://id.example/jwks',
                'issuer' => 'https://id.example',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://id.example/authorize', $result['authorization_endpoint']);
        self::assertContains(
            'openidconnect: discovery document missing optional endpoint used by later flow steps',
            array_column(DI::logger()->warnings, 0)
        );
        self::assertSame('token_endpoint', DI::logger()->warnings[array_key_last(DI::logger()->warnings)][1]['missing']);
    }

    public function testGetLogsWarningWhenUserinfoEndpointMissingButReturnsConfig(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'authorization_endpoint' => 'https://id.example/authorize',
                'token_endpoint' => 'https://id.example/token',
                'jwks_uri' => 'https://id.example/jwks',
                'issuer' => 'https://id.example',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://id.example/authorize', $result['authorization_endpoint']);
        self::assertContains(
            'openidconnect: discovery document missing optional endpoint used by later flow steps',
            array_column(DI::logger()->warnings, 0)
        );
        self::assertSame('userinfo_endpoint', DI::logger()->warnings[array_key_last(DI::logger()->warnings)][1]['missing']);
    }

    public function testGetLogsWarningWhenJwksUriMissingButReturnsConfig(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'authorization_endpoint' => 'https://id.example/authorize',
                'token_endpoint' => 'https://id.example/token',
                'userinfo_endpoint' => 'https://id.example/userinfo',
                'issuer' => 'https://id.example',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://id.example/authorize', $result['authorization_endpoint']);
        self::assertContains(
            'openidconnect: discovery document missing optional endpoint used by later flow steps',
            array_column(DI::logger()->warnings, 0)
        );
        self::assertSame('jwks_uri', DI::logger()->warnings[array_key_last(DI::logger()->warnings)][1]['missing']);
    }

    public function testGetLogsWarningWhenIssuerMissingButStillReturnsConfig(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'authorization_endpoint' => 'https://id.example/authorize',
                'token_endpoint' => 'https://id.example/token',
                'userinfo_endpoint' => 'https://id.example/userinfo',
                'jwks_uri' => 'https://id.example/jwks',
                'issuer' => " \n\t ",
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame('https://id.example/authorize', $result['authorization_endpoint']);
        self::assertContains(
            'openidconnect: discovery document has no issuer claim, proceeding with derived issuer validation from discovery_url',
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

    public function testGetFailureLogRedactsBearerTokenFromDiscoveryBodySnippet(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            false,
            '{"email":"person@example.test","sub":"subject-42","client_secret":"very-secret","message":"Authorization failed: Bearer super-secret-token-value"}',
            401
        );

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        $contextEncoded = json_encode($lastError[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('super-secret-token-value', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
        self::assertStringNotContainsString('subject-42', $contextEncoded);
        self::assertStringNotContainsString('very-secret', $contextEncoded);
        self::assertStringContainsString('Bearer [redacted]', (string) ($lastError[1]['body'] ?? ''));
    }

    public function testGetFailureLogTruncatesBodySnippetToAtMost1024Characters(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        $body = str_repeat('x', 1200) . 'token-after-cutoff-should-not-appear';
        DI::httpClient()->nextGetResponse = new TestHttpResponse(false, $body, 500);

        $result = (new ProviderConfiguration())->get();

        self::assertSame([], $result);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        $loggedBody = (string) ($lastError[1]['body'] ?? '');
        self::assertLessThanOrEqual(1024, mb_strlen($loggedBody));
        self::assertStringNotContainsString('token-after-cutoff-should-not-appear', $loggedBody);
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

    // --- sanitizeSensitiveString (private via reflection) ---

    public function testSanitizeSensitiveStringRedactsSensitiveJsonFields(): void
    {
        $sanitized = $this->invokeSanitizeSensitiveString('{"token":"t","email":"e@example.test","sub":"s","client_secret":"c","access_token":"a","refresh_token":"r","id_token":"i"}');

        self::assertStringContainsString('"token":"[redacted]"', $sanitized);
        self::assertStringContainsString('"email":"[redacted]"', $sanitized);
        self::assertStringContainsString('"sub":"[redacted]"', $sanitized);
        self::assertStringContainsString('"client_secret":"[redacted]"', $sanitized);
        self::assertStringContainsString('"access_token":"[redacted]"', $sanitized);
        self::assertStringContainsString('"refresh_token":"[redacted]"', $sanitized);
        self::assertStringContainsString('"id_token":"[redacted]"', $sanitized);
        self::assertStringNotContainsString('e@example.test', $sanitized);
    }

    public function testSanitizeSensitiveStringRedactsBearerAndTokenPrefixForms(): void
    {
        $sanitized = $this->invokeSanitizeSensitiveString('Authorization: Bearer abc123 and token xyz987');

        self::assertStringContainsString('Bearer [redacted]', $sanitized);
        self::assertStringContainsString('token [redacted]', $sanitized);
        self::assertStringNotContainsString('abc123', $sanitized);
        self::assertStringNotContainsString('xyz987', $sanitized);
    }

    public function testSanitizeSensitiveStringLeavesNonSensitiveTextUnchanged(): void
    {
        $input = 'provider metadata contains no secret fields';
        $sanitized = $this->invokeSanitizeSensitiveString($input);

        self::assertSame($input, $sanitized);
    }

    public function testSanitizeSensitiveStringFallbackSafetyWithNormalValidInput(): void
    {
        $input = '{"token":"abc"} Bearer def';
        $sanitized = $this->invokeSanitizeSensitiveString($input);

        self::assertNotSame('[redacted]', $sanitized);
        self::assertStringContainsString('[redacted]', $sanitized);
    }

    private function invokeSanitizeSensitiveString(string $input): string
    {
        $ref = new \ReflectionClass(ProviderConfiguration::class);
        $method = $ref->getMethod('sanitizeSensitiveString');

        return $method->invoke(new ProviderConfiguration(), $input);
    }
}
