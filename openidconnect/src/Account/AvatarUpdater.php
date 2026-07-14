<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Account;

use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;

final class AvatarUpdater
{
    private const MAX_AVATAR_BYTES = 5 * 1024 * 1024;

    /** @var null|callable(string): array<int, string> */
    private $hostIpResolver;

    public function __construct(?callable $hostIpResolver = null)
    {
        $this->hostIpResolver = $hostIpResolver;
    }

    public function isSafeUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);
        if (empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        if (!empty($parsed['user']) || !empty($parsed['pass'])) {
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

        $ips = $this->resolveHostIps($host);
        if (empty($ips)) {
            return false;
        }

        $hasPublicIp = false;
        foreach ($ips as $ip) {
            if ($ip === '') {
                return false;
            }

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }

            $hasPublicIp = true;
        }

        return $hasPublicIp;
    }

    public function update(int $uid, string $url): void
    {
        if ($url === '') {
            return;
        }

        if (!$this->passesAvatarUrlGuards($uid, $url)) {
            return;
        }

        $photoData = $this->fetchValidatedAvatarPayload($uid, $url);
        if ($photoData === null) {
            return;
        }

        $tempFile = $this->writeAvatarPayloadToTempFile($uid, $photoData);
        if ($tempFile === null) {
            return;
        }

        try {
            $this->applyAvatarToSelfContact($uid, $tempFile);
        } finally {
            $this->deleteTemporaryFile($tempFile, $uid);
        }
    }

    public function isWithinDownloadSizeLimit(string $url, int $maxBytes): bool
    {
        $headers = @get_headers($url, true);
        if (!is_array($headers)) {
            return true;
        }

        $contentLengthHeader = $headers['Content-Length'] ?? $headers['content-length'] ?? null;
        if (is_array($contentLengthHeader)) {
            $contentLengthHeader = end($contentLengthHeader);
        }

        if ($contentLengthHeader === null || $contentLengthHeader === '') {
            return true;
        }

        $contentLength = (int)$contentLengthHeader;
        if ($contentLength <= 0) {
            return true;
        }

        return $contentLength <= $maxBytes;
    }

    public function deleteTemporaryFile(string $path, int $uid): void
    {
        if ($path === '' || !is_file($path)) {
            return;
        }

        $realTempDir = realpath(sys_get_temp_dir());
        $realTempFile = realpath($path);
        if ($realTempDir === false || $realTempFile === false) {
            DI::logger()->warning('openidconnect: failed to resolve temp avatar path for cleanup', ['uid' => $uid, 'tmp' => $path]);
            return;
        }

        $tempDirPrefix = rtrim($realTempDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realTempFile, $tempDirPrefix)) {
            DI::logger()->warning('openidconnect: refused to unlink avatar temp file outside temp dir', ['uid' => $uid, 'tmp' => $realTempFile]);
            return;
        }

        if (strpos(basename($realTempFile), 'avatar_') !== 0) {
            DI::logger()->warning('openidconnect: refused to unlink unexpected temp avatar filename', ['uid' => $uid, 'tmp' => $realTempFile]);
            return;
        }

        try {
            // nosemgrep: php.lang.security.unlink-use.unlink-use
            if (!unlink($realTempFile)) {
                DI::logger()->warning('openidconnect: failed to unlink avatar temp file', ['uid' => $uid, 'tmp' => $realTempFile]);
            }
        } catch (\Throwable $e) {
            DI::logger()->warning('openidconnect: exception while unlinking avatar temp file', [
                'uid' => $uid,
                'tmp' => $realTempFile,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function resolveHostIps(string $host): array
    {
        if ($this->hostIpResolver !== null) {
            try {
                $ips = ($this->hostIpResolver)($host);
            } catch (\Throwable $e) {
                DI::logger()->warning('openidconnect: host IP resolver failed', ['host' => $host, 'error' => $e->getMessage()]);
                return [];
            }

            return array_values(array_filter(array_map('strval', $ips), static fn(string $ip): bool => $ip !== ''));
        }

        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
        $ips = array_values(array_filter(array_map(
            static fn(array $record): string => (string)($record['ip'] ?? $record['ipv6'] ?? ''),
            $records
        )));

        if (!empty($ips)) {
            return $ips;
        }

        $fallback = gethostbyname($host);
        if ($fallback === $host) {
            return [];
        }

        return [$fallback];
    }

    private function sanitizeSensitiveUrl(string $url): string
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return '[redacted-url]';
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            unset($parts['user'], $parts['pass']);
        }

        if (isset($parts['query']) && $parts['query'] !== '') {
            parse_str($parts['query'], $query);
            foreach ($query as $key => $value) {
                if (preg_match('/token|secret|email|sub/i', (string) $key)) {
                    $query[$key] = '[redacted]';
                }
            }

            $parts['query'] = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';

        return $scheme . $host . $port . $path . $query . $fragment;
    }

    private function passesAvatarUrlGuards(int $uid, string $url): bool
    {
        if (!$this->isSafeUrl($url)) {
            DI::logger()->warning('openidconnect: rejected unsafe picture URL', ['url' => $this->sanitizeSensitiveUrl($url)]);
            return false;
        }

        if (!$this->isWithinDownloadSizeLimit($url, self::MAX_AVATAR_BYTES)) {
            DI::logger()->warning('openidconnect: rejected avatar URL with oversized content-length', [
                'uid' => $uid,
                'url' => $this->sanitizeSensitiveUrl($url),
                'max_bytes' => self::MAX_AVATAR_BYTES,
            ]);
            return false;
        }

        return true;
    }

    private function fetchValidatedAvatarPayload(int $uid, string $url): ?string
    {
        try {
            $photoData = DI::httpClient()->fetch($url, '', 30);
        } catch (\Throwable $e) {
            DI::logger()->warning('openidconnect: failed to fetch avatar image', [
                'uid' => $uid,
                'url' => $this->sanitizeSensitiveUrl($url),
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (empty($photoData)) {
            DI::logger()->warning('openidconnect: avatar fetch returned empty payload', ['uid' => $uid, 'url' => $this->sanitizeSensitiveUrl($url)]);
            return null;
        }

        if (strlen($photoData) > self::MAX_AVATAR_BYTES) {
            DI::logger()->warning('openidconnect: rejected oversized avatar payload', [
                'uid' => $uid,
                'url' => $this->sanitizeSensitiveUrl($url),
                'bytes' => strlen($photoData),
                'max_bytes' => self::MAX_AVATAR_BYTES,
            ]);
            return null;
        }

        return $photoData;
    }

    private function writeAvatarPayloadToTempFile(int $uid, string $photoData): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
        if ($tempFile === false) {
            DI::logger()->warning('openidconnect: failed to allocate temporary avatar file', ['uid' => $uid]);
            return null;
        }

        $written = @file_put_contents($tempFile, $photoData);
        if ($written === false) {
            DI::logger()->warning('openidconnect: failed to write avatar temp file', ['uid' => $uid, 'tmp' => $tempFile]);
            $this->deleteTemporaryFile($tempFile, $uid);
            return null;
        }

        return $tempFile;
    }

    private function applyAvatarToSelfContact(int $uid, string $tempFile): void
    {
        try {
            $contact = DBA::selectFirst('contact', ['id'], ['uid' => $uid, 'self' => true]);
            if ($contact) {
                Contact::updateAvatar($contact['id'], $tempFile);
            }
        } catch (\Exception $e) {
            DI::logger()->warning('Failed to update avatar', ['uid' => $uid, 'exception' => $e->getMessage()]);
        }
    }
}
