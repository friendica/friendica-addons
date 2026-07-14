<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

use Friendica\DI;

final class LoginPolicy
{
    public const STATE_BYTES = 32;
    public const NONCE_BYTES = 32;
    public const PKCE_VERIFIER_BYTES = 48;
    public const NO_AUTO_QUERY_KEY = 'openidconnect_no_auto';
    public const LOGOUT_NO_AUTO_COOKIE = 'openidconnect_no_auto_logout';

    public static function sanitizeReturnPath(string $returnPath): string
    {
        if ($returnPath === '') {
            return '';
        }

        // Reject absolute URLs and URIs with a scheme (e.g. https://, mona://, //)
        if (preg_match('#^(https?:)?//#i', $returnPath) || preg_match('#^[a-z][a-z0-9+.-]*:#i', $returnPath)) {
            DI::logger()->warning('openidconnect: rejected absolute return_path', ['path' => $returnPath]);
            return '';
        }

        return ltrim($returnPath, '/');
    }

    public static function isBearerRequest(array $server): bool
    {
        $authorization = $server['HTTP_AUTHORIZATION'] ?? '';

        if (!is_string($authorization) || $authorization === '') {
            return false;
        }

        return preg_match('/^Bearer\s+/i', $authorization) === 1;
    }

    public static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public static function generateState(): string
    {
        return bin2hex(random_bytes(self::STATE_BYTES));
    }

    public static function generateNonce(): string
    {
        return bin2hex(random_bytes(self::NONCE_BYTES));
    }

    public static function generatePkceVerifier(): string
    {
        return self::base64UrlEncode(random_bytes(self::PKCE_VERIFIER_BYTES));
    }

    public static function generatePkceChallenge(string $verifier): string
    {
        return self::base64UrlEncode(hash('sha256', $verifier, true));
    }

    public static function shouldAutoRedirect(array $query, array $server, bool $transparentSso): bool
    {
        if (!$transparentSso) {
            return false;
        }

        if (strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            return false;
        }

        if (!empty($query[self::NO_AUTO_QUERY_KEY]) || !empty($query['error'])) {
            return false;
        }

        // Suppress transparent SSO briefly after logout to avoid immediate re-login loops.
        if (!empty($_COOKIE[self::LOGOUT_NO_AUTO_COOKIE])) {
            return false;
        }

        if (self::isBearerRequest($server)) {
            return false;
        }

        return true;
    }

    public static function buildFallbackPath(string $returnPath): string
    {
        $params = [self::NO_AUTO_QUERY_KEY => 1];
        $returnPath = self::sanitizeReturnPath($returnPath);

        if ($returnPath !== '') {
            $params['return_path'] = $returnPath;
        }

        return 'login?' . http_build_query($params);
    }
}

