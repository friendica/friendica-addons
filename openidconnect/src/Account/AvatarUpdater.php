<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Account;

use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;

final class AvatarUpdater
{
    public function isSafeUrl(string $url): bool
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

    public function update(int $uid, string $url): void
    {
        if ($url === '') {
            return;
        }

        if (!$this->isSafeUrl($url)) {
            DI::logger()->warning('openidconnect: rejected unsafe picture URL', ['url' => $url]);
            return;
        }

        try {
            $photoData = DI::httpClient()->fetch($url, '', 30);
        } catch (\Throwable $e) {
            DI::logger()->warning('openidconnect: failed to fetch avatar image', [
                'uid' => $uid,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        if (empty($photoData)) {
            DI::logger()->warning('openidconnect: avatar fetch returned empty payload', ['uid' => $uid, 'url' => $url]);
            return;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
        if ($tempFile === false) {
            DI::logger()->warning('openidconnect: failed to allocate temporary avatar file', ['uid' => $uid]);
            return;
        }

        $written = @file_put_contents($tempFile, $photoData);
        if ($written === false) {
            DI::logger()->warning('openidconnect: failed to write avatar temp file', ['uid' => $uid, 'tmp' => $tempFile]);
            $this->deleteTemporaryFile($tempFile, $uid);
            return;
        }

        try {
            $contact = DBA::selectFirst('contact', ['id'], ['uid' => $uid, 'self' => true]);
            if ($contact) {
                Contact::updateAvatar($contact['id'], $tempFile);
            }
        } catch (\Exception $e) {
            DI::logger()->warning('Failed to update avatar', ['uid' => $uid, 'exception' => $e->getMessage()]);
        } finally {
            $this->deleteTemporaryFile($tempFile, $uid);
        }
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
}
