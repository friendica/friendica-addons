<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Account;

use Friendica\Database\DBA;
use Friendica\DI;

final class AccountLinker
{
    public function get(int $uid): ?array
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

    public function link(int $uid, string $sub, string $email, string $nickname): bool
    {
        if (empty($sub)) {
            DI::logger()->warning('openidconnect_link_user: refused empty sub', ['uid' => $uid]);
            return false;
        }

        $existingOwner = DBA::selectFirst('user', ['uid'], ['openid' => $sub]);
        if (!empty($existingOwner['uid']) && (int)$existingOwner['uid'] !== $uid) {
            DI::logger()->warning('openidconnect_link_user: subject already linked to another uid', [
                'has_sub' => $sub !== '',
                'owner_uid' => $existingOwner['uid'],
                'uid' => $uid,
            ]);
            return false;
        }

        DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

        DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
        DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
        DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

        DI::logger()->info('OpenID Connect account linked', ['uid' => $uid, 'has_sub' => $sub !== '']);

        return true;
    }

    public function unlink(int $uid): void
    {
        DBA::update('user', ['openid' => ''], ['uid' => $uid]);
        DI::pConfig()->delete($uid, 'openidconnect', 'oidc_sub');
        DI::pConfig()->delete($uid, 'openidconnect', 'oidc_email');
        DI::pConfig()->delete($uid, 'openidconnect', 'oidc_nickname');
        DI::session()->remove('openidconnect_tokens');
    }
}
