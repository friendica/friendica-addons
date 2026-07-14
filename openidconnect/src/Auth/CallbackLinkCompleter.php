<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

use Closure;
use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Database\DBA;
use Friendica\DI;

final class CallbackLinkCompleter
{
    private AccountLinker $accountLinker;
    private readonly Closure $ownerLookup;

    public function __construct(?AccountLinker $accountLinker = null, ?callable $ownerLookup = null)
    {
        $this->accountLinker = $accountLinker ?? new AccountLinker();
        $this->ownerLookup = $ownerLookup instanceof Closure
            ? $ownerLookup
            : Closure::fromCallable($ownerLookup ?? fn(string $sub): ?array => DBA::selectFirst('user', ['uid'], ['openid' => $sub]));
    }

    public function complete(string $sub, string $email, string $nickname, string $returnPath, array $tokens): void
    {
        $userId = DI::userSession()->getLocalUserId();
        if (!$userId) {
            DI::sysmsg()->addNotice(DI::l10n()->t('You must be logged in to link your account.'));
            DI::baseUrl()->redirect('login');
            return;
        }

        if (empty($sub)) {
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: provider did not return a stable subject identifier.'));
            DI::baseUrl()->redirect('settings/account');
            return;
        }

        $existingOwner = ($this->ownerLookup)($sub);
        if (!empty($existingOwner['uid']) && (int)$existingOwner['uid'] !== (int)$userId) {
            DI::logger()->warning('openidconnect: refusing to link subject already linked to another account', [
                'has_sub' => $sub !== '',
                'owner_uid' => $existingOwner['uid'],
                'attempted_uid' => $userId,
            ]);
            DI::sysmsg()->addNotice(DI::l10n()->t('This identity is already linked to another account.'));
            DI::baseUrl()->redirect('settings/account');
            return;
        }

        if (!$this->accountLinker->link($userId, $sub, $email, $nickname)) {
            DI::logger()->warning('openidconnect: link mode failed to store user link', ['uid' => $userId, 'has_sub' => $sub !== '']);
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect account link failed. Please try again or contact the administrator.'));
            DI::baseUrl()->redirect('settings/account');
            return;
        }

        DI::session()->set('openidconnect_tokens', $tokens);
        session_write_close();

        DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect account successfully linked.'));
        DI::baseUrl()->redirect($returnPath);
    }
}
