<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Account;

use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;

final class UserProvisioner
{
    private AccountLinker $linker;
    private AvatarUpdater $avatarUpdater;

    public function __construct(?AccountLinker $linker = null, ?AvatarUpdater $avatarUpdater = null)
    {
        $this->linker = $linker ?? new AccountLinker();
        $this->avatarUpdater = $avatarUpdater ?? new AvatarUpdater();
    }

    public function normaliseNickname(string $nickname, string $name, string $email): string
    {
        if (!empty($nickname)) {
            return $nickname;
        }

        $normalized = preg_replace('/[^a-z0-9_-]/i', '', strtolower($name));
        $normalized = substr((string)$normalized, 0, 64);

        if (empty($normalized) || strlen($normalized) < 2) {
            // strstr with before_needle=true is stateless; strtok() modifies global
            // tokeniser state and should not be used for a simple prefix extract.
            $normalized = strstr($email, '@', true) ?: '';
        }

        return $normalized;
    }

    public function findOrCreate(string $sub, string $email, string $name, string $nickname, string $picture): ?array
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
                        'uid' => $uid,
                        'new_email' => $email,
                    ]);
                } else {
                    DI::logger()->info('openidconnect: propagating email change from IdP', [
                        'uid' => $uid,
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
                $this->avatarUpdater->update($uid, $picture);
            }

            return $linkedBySub;
        }

        // Select only the columns needed for the email-match check.  Fetching the
        // full row here would load the password hash and private key into scope
        // unnecessarily.  The full row is re-fetched later when actually needed.
        $existingUser = DBA::selectFirst('user', ['uid', 'openid'], ['email' => $email]);
        if ($existingUser) {
            // Auto-link when the existing account was never linked to any IdP and auto-create is on.
            if (empty($existingUser['openid']) && DI::config()->get('openidconnect', 'auto_create_accounts')) {
                DI::logger()->info('openidconnect: auto-linking existing unlinked account by email', ['uid' => $existingUser['uid'], 'sub' => $sub]);
                $this->linker->link($existingUser['uid'], $sub, $email, $nickname);
                if (!empty($picture)) {
                    $this->avatarUpdater->update($existingUser['uid'], $picture);
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
                $result = $this->createUser($sub, $email, $name, $nickname, $picture);
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

    private function createUser(string $sub, string $email, string $name, string $nickname, string $picture): ?array
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

            // Resolve uid before any use — User::create may not include it in the returned array.
            $uid = $user['uid'] ?? DBA::lastInsertId();
            DI::logger()->debug('openidconnect: resolved uid', ['uid' => $uid]);

            if (!$uid) {
                DI::logger()->error('openidconnect: uid=0 after User::create — aborting account creation');
                return null;
            }

            // User::create does not honour the 'openid' key — set it explicitly.
            DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

            if (!empty($picture)) {
                $this->avatarUpdater->update($uid, $picture);
            }

            DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
            DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
            DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

            DI::logger()->info('OpenID Connect user created', ['nickname' => $nickname, 'email' => $email, 'uid' => $uid]);
            $userData = DBA::selectFirst('user', [], ['uid' => $uid]);
            return $userData;
        } catch (\Throwable $e) {
            // Log the full trace here for diagnostic detail, then re-throw so the
            // caller can show the user a proper error notice and redirect — the
            // inner catch must not swallow errors silently or the outer handler
            // becomes dead code.
            DI::logger()->error('openidconnect: User::create failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
