<?php

declare(strict_types=1);

namespace Friendica\Addon\openidconnect\src;

use Friendica\DI;

/**
 * Pure static helper functions for OpenID Connect operations.
 */
final class Utilities
{
	/**
	 * Generate a random state parameter for CSRF protection.
	 */
	public static function generateState(): string
	{
		return bin2hex(random_bytes(OIDC_STATE_LENGTH));
	}

	/**
	 * Generate a random nonce for id_token validation.
	 */
	public static function generateNonce(): string
	{
		return bin2hex(random_bytes(OIDC_NONCE_LENGTH));
	}

	/**
	 * Encode a value using URL-safe base64.
	 */
	public static function base64urlEncode(string $value): string
	{
		return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
	}

	/**
	 * Generate a PKCE code verifier.
	 */
	public static function generatePkceVerifier(): string
	{
		return self::base64urlEncode(random_bytes(OIDC_PKCE_VERIFIER_BYTES));
	}

	/**
	 * Generate a PKCE code challenge from a verifier using S256.
	 */
	public static function generatePkceChallenge(string $verifier): string
	{
		return self::base64urlEncode(hash('sha256', $verifier, true));
	}

	/**
	 * Sanitize a return path to prevent open redirect attacks.
	 */
	public static function sanitizeReturnPath(string $returnPath): string
	{
		if (empty($returnPath)) {
			return '';
		}
		// Reject absolute URLs and URIs with a scheme (e.g. https://, mona://, //)
		if (preg_match('#^(https?:)?//#i', $returnPath) || preg_match('#^[a-z][a-z0-9+.-]*:#i', $returnPath)) {
			DI::logger()->warning('openidconnect: rejected absolute return_path', ['path' => $returnPath]);
			return '';
		}
		return ltrim($returnPath, '/');
	}

	/**
	 * Check whether the current request uses a Bearer token.
	 */
	public static function isBearerRequest(array $server): bool
	{
		$authorization = $server['HTTP_AUTHORIZATION'] ?? '';

		if (!is_string($authorization) || $authorization === '') {
			return false;
		}

		return preg_match('/^Bearer\s+/i', $authorization) === 1;
	}

	/**
	 * Get the cookie path for the current installation.
	 */
	public static function getCookiePath(): string
	{
		$path = trim(DI::baseUrl()->getPath(), '/');

		if ($path === '') {
			return '/';
		}

		return '/' . $path . '/';
	}

	/**
	 * Check whether the current request is over HTTPS (directly or via proxy).
	 */
	public static function isSecureRequest(): bool
	{
		$https = $_SERVER['HTTPS'] ?? '';
		$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

		if (is_string($https) && $https !== '' && strtolower($https) !== 'off') {
			return true;
		}

		return is_string($forwardedProto) && strtolower($forwardedProto) === 'https';
	}

	/**
	 * Check whether a URL is safe to fetch (prevents SSRF).
	 */
	public static function isSafeUrl(string $url): bool
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}
		$parsed = parse_url($url);
		if (empty($parsed['scheme']) || empty($parsed['host'])) {
			return false;
		}
		$host = $parsed['host'];

		// Trust any URL on the same host as the configured IdP (covers self-hosted/private Authentik).
		$idpHost = parse_url(DI::config()->get('openidconnect', 'discovery_url') ?? '', PHP_URL_HOST);
		if ($idpHost && $host === $idpHost) {
			return true;
		}

		if (($parsed['scheme'] ?? '') !== 'https') {
			return false;
		}

		// Use dns_get_record to handle both A and AAAA; fall back to gethostbyname.
		$records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
		$ips = array_map(fn($r) => $r['ip'] ?? $r['ipv6'] ?? '', $records);
		if (empty($ips)) {
			$ips = [gethostbyname($host)];
		}
		foreach ($ips as $ip) {
			if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
				return true; // at least one public IP — allow
			}
		}
		return false;
	}

	/**
	 * Normalize a nickname from OIDC claims, falling back to email prefix.
	 */
	public static function normalizeNickname(string $nickname, string $name, string $email): string
	{
		if (!empty($nickname)) {
			return $nickname;
		}

		$normalized = preg_replace('/[^a-z0-9_-]/i', '', strtolower($name));
		$normalized  = substr($normalized, 0, 64);

		if (empty($normalized) || strlen($normalized) < 2) {
			// strstr with before_needle=true is stateless; strtok() modifies global
			// tokeniser state and should not be used for a simple prefix extract.
			$normalized = strstr($email, '@', true) ?: '';
		}

		return $normalized;
	}

	/**
	 * Extract the error value from an authorization response query.
	 */
	public static function getAuthorizationError(array $query): string
	{
		$error = $query['error'] ?? $query['err'] ?? '';

		return is_string($error) ? $error : '';
	}

	/**
	 * Check whether a silent-auth error should fall back to manual login.
	 */
	public static function shouldFallbackToManualLogin(string $error): bool
	{
		return in_array($error, [
			'login_required',
			'interaction_required',
			'consent_required',
			'account_selection_required',
		], true);
	}

	/**
	 * Check whether transparent SSO auto-redirect should fire.
	 */
	public static function shouldAutoRedirectLogin(array $query = [], array $server = []): bool
	{
		if (!DI::config()->get('openidconnect', 'transparent_sso')) {
			return false;
		}

		if (strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
			return false;
		}

		if (!empty($query['openidconnect_no_auto']) || !empty($query['error'])) {
			return false;
		}

		// Suppress transparent SSO briefly after logout to avoid immediate re-login loops.
		if (!empty($_COOKIE[OIDC_LOGOUT_NO_AUTO_COOKIE])) {
			return false;
		}

		if (self::isBearerRequest($server)) {
			return false;
		}

		return true;
	}

	/**
	 * Build a login fallback URL that disables transparent SSO.
	 */
	public static function buildLoginFallbackPath(string $returnPath = ''): string
	{
		$params = ['openidconnect_no_auto' => 1];
		$returnPath = self::sanitizeReturnPath($returnPath);

		if ($returnPath !== '') {
			$params['return_path'] = $returnPath;
		}

		return 'login?' . http_build_query($params);
	}
}
