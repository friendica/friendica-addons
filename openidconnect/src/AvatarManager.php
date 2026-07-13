<?php

declare(strict_types=1);

namespace Friendica\Addon\openidconnect\src;

use Friendica\DI;
use Friendica\Database\DBA;
use Friendica\Model\Contact;

/**
 * Handles fetching and updating user avatars from an external URL.
 */
final class AvatarManager
{
	/**
	 * Fetch an avatar from a URL and set it as the user's profile picture.
	 */
	public static function updateAvatar(int $uid, string $pictureUrl): void
	{
		if (empty($pictureUrl)) {
			return;
		}

		if (!Utilities::isSafeUrl($pictureUrl)) {
			DI::logger()->warning('openidconnect: rejected unsafe picture URL', ['url' => $pictureUrl]);
			return;
		}

		try {
			$photoData = DI::httpClient()->fetch($pictureUrl, '', 30);
		} catch (\Throwable $e) {
			DI::logger()->warning('openidconnect: failed to fetch avatar image', [
				'uid' => $uid,
				'url' => $pictureUrl,
				'error' => $e->getMessage(),
			]);
			return;
		}

		if (empty($photoData)) {
			DI::logger()->warning('openidconnect: avatar fetch returned empty payload', ['uid' => $uid, 'url' => $pictureUrl]);
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
			self::deleteTempFile($tempFile, $uid);
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
			// Always remove the temp file; check existence first to avoid a PHP
			// warning when tempnam() failed or the file was already cleaned up.
			self::deleteTempFile($tempFile, $uid);
		}
	}

	/**
	 * Safely delete a temporary avatar file after validation.
	 */
	public static function deleteTempFile(string $tempFile, int $uid): void
	{
		if ($tempFile === '' || !is_file($tempFile)) {
			return;
		}

		$realTempDir = realpath(sys_get_temp_dir());
		$realTempFile = realpath($tempFile);
		if ($realTempDir === false || $realTempFile === false) {
			DI::logger()->warning('openidconnect: failed to resolve temp avatar path for cleanup', ['uid' => $uid, 'tmp' => $tempFile]);
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
