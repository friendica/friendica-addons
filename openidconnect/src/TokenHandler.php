<?php

declare(strict_types=1);

namespace Friendica\Addon\openidconnect\src;

use Friendica\DI;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;

/**
 * Handles OAuth2 token exchange, JWT validation, and token revocation.
 */
final class TokenHandler
{
	/**
	 * Exchange an authorization code for tokens at the provider's token endpoint.
	 */
	public static function exchangeCode(string $code, string $codeVerifier = ''): array
	{
		$config = ProviderConfig::getProviderConfig();
		if (empty($config['token_endpoint'])) {
			return [];
		}

		$clientId = DI::config()->get('openidconnect', 'client_id');
		$clientSecret = DI::config()->get('openidconnect', 'client_secret');
		$redirectUri = DI::baseUrl() . '/openidconnect/callback';
		$authMethod = ProviderConfig::getClientAuthMethod($config, 'token');

		$postData = [
			'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => $redirectUri,
		];
		if (!empty($codeVerifier)) {
			$postData['code_verifier'] = $codeVerifier;
		}

		$headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
		if ($authMethod === 'client_secret_basic') {
			$headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
		} else {
			$postData['client_id'] = $clientId;
			$postData['client_secret'] = $clientSecret;
		}

		try {
			$response = DI::httpClient()->post($config['token_endpoint'], $postData, $headers, 30);
		} catch (\Throwable $e) {
			DI::logger()->error('openidconnect: token endpoint request threw exception', [
				'endpoint' => $config['token_endpoint'],
				'error' => $e->getMessage(),
			]);
			return [];
		}

		if (!$response->isSuccess()) {
			DI::logger()->error('Token endpoint returned error', ['code' => $response->getReturnCode(), 'response' => $response->getBodyString()]);
			return [];
		}

		try {
			$tokens = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			DI::logger()->error('openidconnect: malformed JSON in token response', ['error' => $e->getMessage()]);
			return [];
		}
		if (!isset($tokens['access_token'])) {
			DI::logger()->error('openidconnect: token response missing access_token', [
				'keys' => array_keys($tokens),
			]);
			return [];
		}

		return $tokens;
	}

	/**
	 * Validate a JWT id_token against the provider's JWKS and OIDC claims.
	 */
	public static function validateIdToken(string $idToken, string $expectedNonce = '', string $accessToken = ''): object|false
	{
		if (empty($idToken)) {
			DI::logger()->warning('openidconnect: id_token missing from token response');
			return false;
		}

		$config = ProviderConfig::getProviderConfig();
		$jwksUri = $config['jwks_uri'] ?? '';
		if (empty($jwksUri)) {
			DI::logger()->error('openidconnect: jwks_uri missing from discovery document');
			return false;
		}

		$cacheKey = 'openidconnect:jwks';
		$jwksData = DI::cache()->get($cacheKey);
		if (empty($jwksData)) {
			$response = DI::httpClient()->get($jwksUri, '', [
				HttpClientOptions::TIMEOUT => 15,
			]);
			if (!$response->isSuccess()) {
				DI::logger()->error('openidconnect: failed to fetch JWKS', ['uri' => $jwksUri]);
				return false;
			}
			try {
				$jwksData = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
			} catch (\JsonException $e) {
				DI::logger()->error('openidconnect: malformed JSON in JWKS response', [
					'uri'   => $jwksUri,
					'error' => $e->getMessage(),
				]);
				return false;
			}
			if (empty($jwksData['keys'])) {
				DI::logger()->error('openidconnect: JWKS response missing keys array');
				return false;
			}
			DI::cache()->set($cacheKey, $jwksData, \Friendica\Core\Cache\Enum\Duration::DAY);
		}

		JWT::$leeway = 60;

		try {
			$keySet  = JWK::parseKeySet($jwksData, 'RS256');
			$decoded = JWT::decode($idToken, $keySet);
		} catch (SignatureInvalidException $e) {
			// Bust the JWKS cache and retry once in case Authentik rotated its key.
			static $jwksRetried = false;
			if (!$jwksRetried) {
				$jwksRetried = true;
				DI::cache()->delete('openidconnect:jwks');
				DI::logger()->warning('openidconnect: signature invalid — busting JWKS cache and retrying');
				return self::validateIdToken($idToken, $expectedNonce, $accessToken);
			}
			DI::logger()->warning('openidconnect: id_token signature invalid after JWKS refresh');
			return false;
		} catch (ExpiredException $e) {
			DI::logger()->warning('openidconnect: id_token expired');
			return false;
		} catch (BeforeValidException $e) {
			DI::logger()->warning('openidconnect: id_token not yet valid');
			return false;
		} catch (\UnexpectedValueException $e) {
			DI::logger()->warning('openidconnect: id_token malformed', ['error' => $e->getMessage()]);
			return false;
		} catch (\InvalidArgumentException $e) {
			DI::logger()->error('openidconnect: JWKS key configuration error', ['error' => $e->getMessage()]);
			return false;
		}

		$expectedIss = rtrim(DI::config()->get('openidconnect', 'discovery_url'), '/');
		$expectedIss = preg_replace('#/\.well-known/openid-configuration$#', '', $expectedIss);
		$expectedAud = DI::config()->get('openidconnect', 'client_id');

		if (rtrim($decoded->iss ?? '', '/') !== rtrim($expectedIss, '/')) {
			DI::logger()->warning('openidconnect: id_token iss mismatch', [
				'expected' => $expectedIss,
				'got'      => $decoded->iss ?? '',
			]);
			return false;
		}

		$aud = $decoded->aud ?? '';
		$audList = is_array($aud) ? $aud : [$aud];
		if (!in_array($expectedAud, $audList, true)) {
			DI::logger()->warning('openidconnect: id_token aud mismatch', [
				'expected' => $expectedAud,
				'got'      => $aud,
			]);
			return false;
		}

		if ($expectedNonce !== '' && ($decoded->nonce ?? '') !== $expectedNonce) {
			DI::logger()->warning('openidconnect: id_token nonce mismatch');
			return false;
		}

		// Validate azp when multiple audiences are present (OIDC Core §2).
		$audList = is_array($decoded->aud ?? '') ? ($decoded->aud ?? []) : [$decoded->aud ?? ''];
		if (count($audList) > 1 && isset($decoded->azp) && $decoded->azp !== $expectedAud) {
			DI::logger()->warning('openidconnect: azp mismatch in multi-audience token', [
				'expected' => $expectedAud,
				'got'      => $decoded->azp,
			]);
			return false;
		}

		// Validate at_hash when present (OIDC Core §3.3.2.11).
		if ($accessToken !== '' && isset($decoded->at_hash)) {
			$halfHash = substr(hash('sha256', $accessToken, true), 0, 16);
			if (!hash_equals(Utilities::base64urlEncode($halfHash), $decoded->at_hash)) {
				DI::logger()->warning('openidconnect: at_hash mismatch — possible access-token substitution attack');
				return false;
			}
		}

		return $decoded;
	}

	/**
	 * Revoke a token at the provider's revocation endpoint.
	 */
	public static function revokeToken(string $endpoint, string $token, array $providerConfig, int $timeout = 30): void
	{
		$clientId     = DI::config()->get('openidconnect', 'client_id');
		$clientSecret = DI::config()->get('openidconnect', 'client_secret');
		$authMethod   = ProviderConfig::getClientAuthMethod($providerConfig, 'revocation');
		$headers      = ['Content-Type' => 'application/x-www-form-urlencoded'];
		$postData     = ['token' => $token];

		if ($authMethod === 'client_secret_basic') {
			$headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
		} else {
			$postData['client_id']     = $clientId;
			$postData['client_secret'] = $clientSecret;
		}

		try {
			$response = DI::httpClient()->post($endpoint, $postData, $headers, $timeout);
		} catch (\Throwable $e) {
			DI::logger()->warning('openidconnect: revocation endpoint request threw exception', [
				'endpoint' => $endpoint,
				'error' => $e->getMessage(),
			]);
			return;
		}

		if (!$response->isSuccess()) {
			DI::logger()->warning('openidconnect: revocation endpoint returned non-success', [
				'endpoint' => $endpoint,
				'code' => $response->getReturnCode(),
				'body' => $response->getBodyString(),
			]);
		}
	}
}
