<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Provider;

use Friendica\Core\Cache\Enum\Duration;
use Friendica\DI;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;

final class ProviderConfiguration
{
    private const CACHE_KEY = 'openidconnect:provider_config';

    public function isConfigured(): bool
    {
        $required = ['client_id', 'client_secret', 'discovery_url'];
        foreach ($required as $key) {
            $value = DI::config()->get('openidconnect', $key);
            if (!is_string($value)) {
                if (empty($value)) {
                    return false;
                }
                continue;
            }

            if (trim($value) === '') {
                return false;
            }
        }
        return true;
    }

    public function get(): array
    {
        $cached = DI::cache()->get(self::CACHE_KEY);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        if ($cached !== null && !is_array($cached)) {
            DI::logger()->warning('openidconnect: provider config cache contained invalid type', ['type' => gettype($cached)]);
            DI::cache()->delete(self::CACHE_KEY);
        }

        $discoveryUrl = DI::config()->get('openidconnect', 'discovery_url');
        if (empty($discoveryUrl)) {
            DI::logger()->error('openidconnect: discovery_url is empty');
            return [];
        }

        if (!filter_var($discoveryUrl, FILTER_VALIDATE_URL)) {
            DI::logger()->error('openidconnect: discovery_url is invalid', ['url' => $discoveryUrl]);
            return [];
        }

        try {
            if (method_exists(DI::httpClient(), 'get')) {
                $options = ['timeout' => 30];
                if (class_exists(HttpClientOptions::class)) {
                    $options = [HttpClientOptions::TIMEOUT => 30];
                }

                $response = DI::httpClient()->get($discoveryUrl, '', [
                    ...$options,
                ]);

                if (!$response->isSuccess()) {
                    DI::logger()->error('openidconnect: failed to fetch OIDC discovery document', [
                        'url' => $discoveryUrl,
                        'code' => $response->getReturnCode(),
                        'body' => $this->sanitizeSensitiveString(mb_substr($response->getBodyString(), 0, 1024)),
                    ]);
                    return [];
                }

                $responseBody = $response->getBodyString();
            } else {
                $responseBody = DI::httpClient()->fetch($discoveryUrl, '', 30);
            }
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: exception while fetching discovery document', [
                'url' => $discoveryUrl,
                'error' => $e->getMessage(),
            ]);
            return [];
        }

        if ($responseBody === '') {
            DI::logger()->error('openidconnect: empty OIDC discovery response body', ['url' => $discoveryUrl]);
            return [];
        }

        try {
            $config = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            DI::logger()->error('openidconnect: malformed JSON in OIDC discovery document', [
                'url'   => $discoveryUrl,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
        if (empty($config['authorization_endpoint']) || !is_string($config['authorization_endpoint'])) {
            DI::logger()->error('openidconnect: invalid OIDC discovery document, missing authorization_endpoint', ['url' => $discoveryUrl]);
            return [];
        }

        foreach (['token_endpoint', 'userinfo_endpoint', 'jwks_uri'] as $optionalEndpointKey) {
            if (empty($config[$optionalEndpointKey]) || !is_string($config[$optionalEndpointKey])) {
                DI::logger()->warning('openidconnect: discovery document missing optional endpoint used by later flow steps', [
                    'url' => $discoveryUrl,
                    'missing' => $optionalEndpointKey,
                ]);
            }
        }

        if (!isset($config['issuer']) || !is_string($config['issuer']) || trim($config['issuer']) === '') {
            DI::logger()->warning('openidconnect: discovery document has no issuer claim, proceeding with derived issuer validation from discovery_url', ['url' => $discoveryUrl]);
        }

        $cacheTtl = class_exists(Duration::class) ? Duration::DAY : 86400;
        try {
            DI::cache()->set(self::CACHE_KEY, $config, $cacheTtl);
        } catch (\Throwable $e) {
            DI::logger()->warning('openidconnect: failed to cache OIDC discovery document', [
                'url' => $discoveryUrl,
                'error' => $e->getMessage(),
            ]);
        }

        return $config;
    }

    private function sanitizeSensitiveString(string $value): string
    {
        $redacted = preg_replace('/("(?:access_token|refresh_token|id_token|client_secret|token|email|sub)"\s*:\s*")([^"]*)(")/i', '$1[redacted]$3', $value);
        $redacted = $redacted ?? '[redacted]';

        return preg_replace('/(Bearer\s+|token\s+)([^\s"\']+)/i', '$1[redacted]', $redacted) ?? '[redacted]';
    }

    public static function clientAuthMethod(array $metadata, string $endpoint = 'token'): string
    {
        $metadataKey = $endpoint === 'revocation'
            ? 'revocation_endpoint_auth_methods_supported'
            : 'token_endpoint_auth_methods_supported';
        $methods = $metadata[$metadataKey] ?? [];

        if (!is_array($methods)) {
            DI::logger()->warning('openidconnect: provider metadata auth methods has invalid type, defaulting to client_secret_basic', [
                'endpoint' => $endpoint,
                'type' => gettype($methods),
            ]);
            return 'client_secret_basic';
        }

        if (empty($methods) || in_array('client_secret_basic', $methods, true)) {
            return 'client_secret_basic';
        }

        return 'client_secret_post';
    }
}
