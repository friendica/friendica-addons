<?php

declare(strict_types=1);

namespace Friendica\Addon\openidconnect\src;

use Friendica\DI;
use Friendica\Core\Cache\Enum\Duration;

/**
 * Handles OpenID Connect discovery document fetching and provider metadata.
 */
final class ProviderConfig
{
	/**
	 * Check whether the addon has the minimum required configuration.
	 */
	public static function isConfigured(): bool
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

	/**
	 * Fetch and cache the OpenID Connect discovery document.
	 */
	public static function getProviderConfig(): array
	{
		$cacheKey = 'openidconnect:provider_config';

		$cached = DI::cache()->get($cacheKey);
		if (is_array($cached) && !empty($cached)) {
			return $cached;
		}

		if ($cached !== null && !is_array($cached)) {
			DI::logger()->warning('openidconnect: provider config cache contained invalid type', ['type' => gettype($cached)]);
			DI::cache()->delete($cacheKey);
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
			$response = DI::httpClient()->fetch($discoveryUrl, '', 30);
		} catch (\Throwable $e) {
			DI::logger()->error('openidconnect: exception while fetching discovery document', [
				'url' => $discoveryUrl,
				'error' => $e->getMessage(),
			]);
			return [];
		}

		if (!$response) {
			DI::logger()->error('Failed to fetch OIDC discovery document', ['url' => $discoveryUrl]);
			return [];
		}

		try {
			$config = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
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

		DI::cache()->set($cacheKey, $config, Duration::DAY);
		return $config;
	}

	/**
	 * Determine the client authentication method for a given endpoint.
	 */
	public static function getClientAuthMethod(array $config, string $endpoint = 'token'): string
	{
		$metadataKey = $endpoint === 'revocation'
			? 'revocation_endpoint_auth_methods_supported'
			: 'token_endpoint_auth_methods_supported';
		$methods = $config[$metadataKey] ?? [];

		if (empty($methods) || in_array('client_secret_basic', $methods, true)) {
			return 'client_secret_basic';
		}

		return 'client_secret_post';
	}
}
