<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Account;

use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;
use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;

final class UserProvisioner
{
    private const MAX_CREATE_RETRIES = 5;

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
            return $this->refreshLinkedUser($linkedBySub, $sub, $email, $nickname, $picture);
        }

        // Select only the columns needed for the email-match check.  Fetching the
        // full row here would load the password hash and private key into scope
        // unnecessarily.  The full row is re-fetched later when actually needed.
        $existingUser = DBA::selectFirst('user', ['uid', 'openid'], ['email' => $email]);
        if ($existingUser) {
            return $this->resolveExistingEmailMatch($existingUser, $sub, $email, $nickname, $picture);
        }

        if ($this->autoCreateAccountsEnabled()) {
            return $this->createUserWhenAllowed($sub, $email, $name, $nickname, $picture);
        }

        DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: No matching account found and registration is not available. Please contact the administrator.'));
        return null;
    }

    /**
     * @param array<string, mixed> $linkedBySub
     * @return array<string, mixed>
     */
    private function refreshLinkedUser(array $linkedBySub, string $sub, string $email, string $nickname, string $picture): array
    {
        $uid = (int)$linkedBySub['uid'];
        DI::logger()->debug('openidconnect: found user by sub (linked)', ['uid' => $uid]);

        $linkedBySub = $this->propagateLinkedUserEmail($linkedBySub, $uid, $email);
        $this->storeLinkedUserMetadata($uid, $sub, $email, $nickname, $picture);

        return $linkedBySub;
    }

    /**
     * @param array<string, mixed> $linkedBySub
     * @return array<string, mixed>
     */
    private function propagateLinkedUserEmail(array $linkedBySub, int $uid, string $email): array
    {
        if ($email === '' || ($linkedBySub['email'] ?? '') === $email) {
            return $linkedBySub;
        }

        if (DBA::exists('user', ['email' => $email])) {
            DI::logger()->warning('openidconnect: cannot propagate email change — address already in use by another account', [
                'uid' => $uid,
                'has_new_email' => $email !== '',
            ]);
            return $linkedBySub;
        }

        DI::logger()->info('openidconnect: propagating email change from IdP', [
            'uid' => $uid,
            'has_old_email' => !empty($linkedBySub['email']),
            'has_new_email' => $email !== '',
        ]);
        DBA::update('user', ['email' => $email], ['uid' => $uid]);
        $linkedBySub['email'] = $email;

        return $linkedBySub;
    }

    private function storeLinkedUserMetadata(int $uid, string $sub, string $email, string $nickname, string $picture): void
    {
        DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
        DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
        DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

        if ($picture !== '') {
            $this->avatarUpdater->update($uid, $picture);
        }
    }

    /**
     * @param array<string, mixed> $existingUser
     */
    private function resolveExistingEmailMatch(array $existingUser, string $sub, string $email, string $nickname, string $picture): ?array
    {
        if (empty($existingUser['openid']) && $this->autoCreateAccountsEnabled()) {
            return $this->autoLinkExistingUser($existingUser, $sub, $email, $nickname, $picture);
        }

        DI::logger()->warning('openidconnect: email matches account linked to different sub - REJECTED', [
            'has_email' => $email !== '',
            'has_existing_openid' => !empty($existingUser['openid']),
            'has_attempted_sub' => $sub !== '',
        ]);
        DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: This account is not linked to your identity provider. Please link your account in the settings or contact the administrator.'));
        return null;
    }

    /**
     * @param array<string, mixed> $existingUser
     * @return array<string, mixed>
     */
    private function autoLinkExistingUser(array $existingUser, string $sub, string $email, string $nickname, string $picture): array
    {
        $uid = (int)$existingUser['uid'];
        DI::logger()->info('openidconnect: auto-linking existing unlinked account by email', ['uid' => $uid, 'sub' => $sub]);
        $this->linker->link($uid, $sub, $email, $nickname);

        if ($picture !== '') {
            $this->avatarUpdater->update($uid, $picture);
        }

        return DBA::selectFirst('user', [], ['uid' => $uid]);
    }

    private function autoCreateAccountsEnabled(): bool
    {
        return (bool) DI::config()->get('openidconnect', 'auto_create_accounts');
    }

    private function createUserWhenAllowed(string $sub, string $email, string $name, string $nickname, string $picture): ?array
    {
        DI::logger()->debug('openidconnect: auto_create is enabled, creating user', ['sub' => $sub, 'email' => $email, 'nickname' => $nickname]);

        try {
            $result = $this->createUser($sub, $email, $name, $nickname, $picture);
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            DI::logger()->error('openidconnect: create_user exception', ['exception' => $errorMsg]);
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Account creation failed: %s', $errorMsg));
            DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath(''));
            return null;
        }

        DI::logger()->debug('openidconnect: create_user result', ['result' => $result]);
        return $result;
    }

    private function createUser(string $sub, string $email, string $name, string $nickname, string $picture): ?array
    {
        $nickname = trim($nickname);

        if ($nickname === '') {
            $nickname = $this->normaliseNickname('', $name, $email);
        }

        $nickname = $this->resolveUniqueNickname($nickname, static fn(string $candidate): bool => DBA::exists('user', ['nickname' => $candidate]));

        $bytes = random_bytes(32);
        $password = base64_encode($bytes);

        for ($attempt = 0; $attempt < self::MAX_CREATE_RETRIES; $attempt++) {
            $candidate = $attempt === 0
                ? $nickname
                : $this->resolveUniqueNickname($nickname . $attempt, static fn(string $nameCandidate): bool => DBA::exists('user', ['nickname' => $nameCandidate]));

            try {
                $user = $this->runInTransaction(function () use ($name, $candidate, $email, $password, $sub): array {
                    DI::logger()->debug('openidconnect: calling User::create', ['email' => $email, 'nickname' => $candidate, 'name' => $name]);
                    return User::create([
                        'username' => $name ?: $candidate,
                        'nickname' => $candidate,
                        'email' => $email,
                        'password' => $password,
                        'verified' => true,
                        'openid' => $sub,
                    ]);
                });

                DI::logger()->debug('openidconnect: User::create returned', ['uid' => $user['uid'] ?? 'MISSING', 'user_keys' => array_keys($user ?: [])]);

                $uid = (int)($user['uid'] ?? 0);
                if ($uid <= 0) {
                    $created = DBA::selectFirst('user', ['uid'], ['openid' => $sub]);
                    $uid = (int)($created['uid'] ?? 0);
                }
                if ($uid <= 0) {
                    $uid = (int)DBA::lastInsertId();
                }

                DI::logger()->debug('openidconnect: resolved uid', ['uid' => $uid]);

                if ($uid <= 0) {
                    DI::logger()->error('openidconnect: uid=0 after User::create — aborting account creation');
                    return null;
                }

                DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

                if (!empty($picture)) {
                    $this->avatarUpdater->update($uid, $picture);
                }

                DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
                DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
                DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $candidate);

                DI::logger()->info('OpenID Connect user created', ['nickname' => $candidate, 'email' => $email, 'uid' => $uid]);
                return DBA::selectFirst('user', [], ['uid' => $uid]);
            } catch (\Throwable $e) {
                $linked = DBA::selectFirst('user', [], ['openid' => $sub]);
                if ($linked) {
                    DI::logger()->warning('openidconnect: createUser race reconciled via existing sub link', ['uid' => $linked['uid'] ?? null]);
                    return $linked;
                }

                if ($this->isRetryableCreateException($e)) {
                    continue;
                }

                DI::logger()->error('openidconnect: User::create failed', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        throw new \RuntimeException('OpenID Connect: Could not create user due to concurrent uniqueness conflicts.');
    }

    public function resolveUniqueNickname(string $baseNickname, callable $exists): string
    {
        $normalizedBase = trim($baseNickname);
        if ($normalizedBase === '') {
            $normalizedBase = 'user';
        }

        $candidate = $normalizedBase;
        $counter = 1;
        while ($exists($candidate) && $counter <= 9999) {
            $candidate = $normalizedBase . $counter;
            $counter++;
        }

        if ($counter > 9999) {
            throw new \RuntimeException('Could not generate a unique nickname for: ' . $normalizedBase);
        }

        return $candidate;
    }

    public function isRetryableCreateException(\Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());
        return str_contains($message, 'duplicate')
            || str_contains($message, 'unique constraint')
            || str_contains($message, '1062')
            || str_contains($message, '23505')
            || str_contains($message, 'nickname')
            || str_contains($message, 'email');
    }

    private function runInTransaction(callable $operation): mixed
    {
        if (method_exists(DBA::class, 'transaction')) {
            return DBA::transaction($operation);
        }

        if (method_exists(DBA::class, 'beginTransaction') && method_exists(DBA::class, 'commit') && method_exists(DBA::class, 'rollback')) {
            DBA::beginTransaction();
            try {
                $result = $operation();
                DBA::commit();
                return $result;
            } catch (\Throwable $e) {
                DBA::rollback();
                throw $e;
            }
        }

        return $operation();
    }
}
