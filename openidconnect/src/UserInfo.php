<?php

declare(strict_types=1);

namespace Friendica\Addon\openidconnect\src;

use Friendica\DI;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;

/**
 * Handles fetching and extracting OpenID Connect user claims.
 */
final class UserInfo
{
	/**
	 * Extract user claims from a validated id_token object.
	 */
	public static function extractFromIdToken(object $claims): array
	{
		$sub = isset($claims->sub) && is_scalar($claims->sub) ? (string)$claims->sub : '';
		$email = isset($claims->email) && is_scalar($claims->email) ? (string)$claims->email : '';
		$name = isset($claims->name) && is_scalar($claims->name) ? (string)$claims->name : '';
		$preferredUsername = '';
		if (isset($claims->preferred_username) && is_scalar($claims->preferred_username)) {
			$preferredUsername = (string)$claims->preferred_username;
		} elseif (isset($claims->nickname) && is_scalar($claims->nickname)) {
			$preferredUsername = (string)$claims->nickname;
		}

		$picture = isset($claims->picture) && is_scalar($claims->picture) ? (string)$claims->picture : '';

		$userinfo = [
			'sub' => $sub,
			'email' => $email,
			'name' => $name,
			'preferred_username' => $preferredUsername,
			'picture' => $picture,
		];

		if (isset($claims->email_verified)) {
			$raw = $claims->email_verified;
			if (is_bool($raw)) {
				$userinfo['email_verified'] = $raw;
			} else {
				$normalized = filter_var($raw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
				if ($normalized !== null) {
					$userinfo['email_verified'] = $normalized;
				}
			}
		}

		return $userinfo;
	}

	/**
	 * Fetch user claims from the provider's userinfo endpoint.
	 */
	public static function fetchUserinfo(string $accessToken): array
	{
		if ($accessToken === '') {
			DI::logger()->error('openidconnect: empty access token passed to userinfo endpoint');
			return [];
		}

		$config = ProviderConfig::getProviderConfig();
		if (empty($config['userinfo_endpoint'])) {
			DI::logger()->error('openidconnect: userinfo_endpoint missing from provider configuration');
			return [];
		}

		try {
			$response = DI::httpClient()->get($config['userinfo_endpoint'], '', [
				HttpClientOptions::HEADERS => ['Authorization' => 'Bearer ' . $accessToken],
				HttpClientOptions::TIMEOUT => 30,
			]);
		} catch (\Throwable $e) {
			DI::logger()->error('openidconnect: userinfo request threw exception', [
				'endpoint' => $config['userinfo_endpoint'],
				'error' => $e->getMessage(),
			]);
			return [];
		}

		if (!$response->isSuccess()) {
			DI::logger()->error('Userinfo endpoint returned error', ['code' => $response->getReturnCode()]);
			return [];
		}

		try {
			$userinfo = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
			return $userinfo ?: [];
		} catch (\JsonException $e) {
			DI::logger()->error('openidconnect: malformed JSON in userinfo response', ['error' => $e->getMessage()]);
			return [];
		}
	}
}
