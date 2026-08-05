<?php

declare(strict_types=1);

namespace Friendica\Addon\openidconnect\src;

use Friendica\DI;
use Friendica\Database\DBA;
use Friendica\Model\User;

/**
 * Handles OpenID Connect user lookup, creation, linking, and unlinking.
 */
final class UserManager
{
	/**
	 * Get the linked OIDC account data for a local user.
	 */
	public static function getLinkedAccount(int $uid): ?array
	{
		$oidcSub = DI::pConfig()->get($uid, 'openidconnect', 'oidc_sub');
		if ($oidcSub) {
			return [
				'sub' => $oidcSub,
				'email' => DI::pConfig()->get($uid, 'openidconnect', 'oidc_email'),
				'nickname' => DI::pConfig()->get($uid, 'openidconnect', 'oidc_nickname'),
			];
		}

		return null;
	}

	/**
	 * Link an OIDC subject identifier to a local user account.
	 */
	public static function linkUser(int $uid, string $sub, string $email, string $nickname): bool
	{
		if (empty($sub)) {
			DI::logger()->warning('openidconnect_link_user: refused empty sub', ['uid' => $uid]);
			return false;
		}

		$existingOwner = DBA::selectFirst('user', ['uid'], ['openid' => $sub]);
		if (!empty($existingOwner['uid']) && (int)$existingOwner['uid'] !== $uid) {
			DI::logger()->warning('openidconnect_link_user: subject already linked to another uid', ['sub' => $sub, 'owner_uid' => $existingOwner['uid'], 'uid' => $uid]);
			return false;
		}

		DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

		DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
		DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
		DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

		DI::logger()->info('OpenID Connect account linked', ['uid' => $uid, 'sub' => $sub]);

		return true;
	}

	/**
	 * Find an existing user by OIDC sub or email, or create a new one.
	 */
	public static function findOrCreateUser(string $sub, string $email, string $name, string $nickname, string $picture): ?array
	{
		$linkedBySub = DBA::selectFirst('user', [], ['openid' => $sub]);
		if ($linkedBySub) {
			$uid = (int)$linkedBySub['uid'];
			DI::logger()->debug('openidconnect: found user by sub (linked)', ['uid' => $uid]);

			// Propagate email change from IdP — the IdP is the authoritative source
			// for email when a user is linked via OIDC.
			if (!empty($email) && $linkedBySub['email'] !== $email) {
				// Guard: refuse if the new email is already owned by another account.
				if (DBA::exists('user', ['email' => $email])) {
					DI::logger()->warning('openidconnect: cannot propagate email change — address already in use by another account', [
						'uid'       => $uid,
						'new_email' => $email,
					]);
				} else {
					DI::logger()->info('openidconnect: propagating email change from IdP', [
						'uid'       => $uid,
						'old_email' => $linkedBySub['email'],
						'new_email' => $email,
					]);
					DBA::update('user', ['email' => $email], ['uid' => $uid]);
					$linkedBySub['email'] = $email;
				}
			}

			DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
			DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
			DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);
			if (!empty($picture)) {
				AvatarManager::updateAvatar($uid, $picture);
			}
			return $linkedBySub;
		}

		// Select only the columns needed for the email-match check.
		$existingUser = DBA::selectFirst('user', ['uid', 'openid'], ['email' => $email]);
		if ($existingUser) {
			// Auto-link when the existing account was never linked to any IdP and auto-create is on.
			if (empty($existingUser['openid']) && DI::config()->get('openidconnect', 'auto_create_accounts')) {
				DI::logger()->info('openidconnect: auto-linking existing unlinked account by email', ['uid' => $existingUser['uid'], 'sub' => $sub]);
				self::linkUser($existingUser['uid'], $sub, $email, $nickname);
				if (!empty($picture)) {
					AvatarManager::updateAvatar($existingUser['uid'], $picture);
				}
				return DBA::selectFirst('user', [], ['uid' => $existingUser['uid']]);
			}
			// Account linked to a DIFFERENT sub — reject.
			DI::logger()->warning('openidconnect: email matches account linked to different sub - REJECTED', ['email' => $email, 'existing_openid' => $existingUser['openid'], 'attempted_sub' => $sub]);
			DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: This account is not linked to your identity provider. Please link your account in the settings or contact the administrator.'));
			return null;
		}

		if (DI::config()->get('openidconnect', 'auto_create_accounts')) {
			DI::logger()->debug('openidconnect: auto_create is enabled, creating user', ['sub' => $sub, 'email' => $email, 'nickname' => $nickname]);
			try {
				$result = self::createUser($sub, $email, $name, $nickname, $picture);
			} catch (\Throwable $e) {
				$errorMsg = $e->getMessage();
				DI::logger()->error('openidconnect: create_user exception', ['exception' => $errorMsg]);
				DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Account creation failed: %s', $errorMsg));
				DI::baseUrl()->redirect('login');
				return null;
			}
			DI::logger()->debug('openidconnect: create_user result', ['result' => $result]);
			return $result;
		}

		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: No matching account found and registration is not available. Please contact the administrator.'));
		return null;
	}

	/**
	 * Create a new local user from OIDC claims.
	 */
	public static function createUser(string $sub, string $email, string $name, string $nickname, string $picture): ?array
	{
		$nickname = trim($nickname);
		if (DBA::exists('user', ['nickname' => $nickname])) {
			$counter = 1;
			$baseNickname = $nickname;
			while (DBA::exists('user', ['nickname' => $nickname]) && $counter <= 9999) {
				$nickname = $baseNickname . $counter;
				$counter++;
			}
			if ($counter > 9999) {
				throw new \RuntimeException('Could not generate a unique nickname for: ' . $baseNickname);
			}
		}

		$bytes = random_bytes(32);
		$password = base64_encode($bytes);

		try {
			DI::logger()->debug('openidconnect: calling User::create', ['email' => $email, 'nickname' => $nickname, 'name' => $name]);
			$user = User::create([
				'username' => $name ?: $nickname,
				'nickname' => $nickname,
				'email' => $email,
				'password' => $password,
				'verified' => true,
				'openid' => $sub,
			]);
			DI::logger()->debug('openidconnect: User::create returned', ['uid' => $user['uid'] ?? 'MISSING', 'user_keys' => array_keys($user ?: [])]);

			$uid = $user['uid'] ?? DBA::lastInsertId();
			DI::logger()->debug('openidconnect: resolved uid', ['uid' => $uid]);

			if (!$uid) {
				DI::logger()->error('openidconnect: uid=0 after User::create — aborting account creation');
				return null;
			}

			// User::create does not honour the 'openid' key — set it explicitly.
			DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

			if (!empty($picture)) {
				AvatarManager::updateAvatar($uid, $picture);
			}

			DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
			DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
			DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

			DI::logger()->info('OpenID Connect user created', ['nickname' => $nickname, 'email' => $email, 'uid' => $uid]);
			$userData = DBA::selectFirst('user', [], ['uid' => $uid]);
			return $userData;
		} catch (\Throwable $e) {
			DI::logger()->error('openidconnect: User::create failed', [
				'exception' => $e->getMessage(),
				'trace'     => $e->getTraceAsString(),
			]);
			throw $e;
		}
	}
}
