<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Presentation;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\BaseModule;
use Friendica\Core\Renderer;
use Friendica\DI;

final class SettingsHook
{
    private AccountLinker $accountLinker;

    public function __construct(?AccountLinker $accountLinker = null)
    {
        $this->accountLinker = $accountLinker ?? new AccountLinker();
    }

    public function append(array &$data): void
    {
        $uid = DI::userSession()->getLocalUserId();
        if (!$uid) {
            return;
        }

        $linkedAccount = $this->accountLinker->get($uid);
    $linkedPayload = $linkedAccount ? ['sub' => $linkedAccount['sub'] ?? ''] : null;
        $baseUrl = (string)DI::baseUrl();

        $tpl = Renderer::getMarkupTemplate('settings.tpl', 'addon/openidconnect/');
        try {
            $confirmJson = json_encode(
                DI::l10n()->t('Are you sure you want to unlink your OpenID Connect account?'),
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            DI::logger()->error('openidconnect: failed to encode unlink confirmation text', ['error' => $e->getMessage()]);
            $confirmJson = '""';
        }

        $data['aside'] = Renderer::replaceMacros($tpl, [
            '$linked' => $linkedPayload,
            '$sub_label' => DI::l10n()->t('OIDC ID:'),
            '$unlink_url' => $baseUrl . '/openidconnect/unlink',
            '$link_url' => $baseUrl . '/openidconnect/link',
            '$unlink_token' => BaseModule::getFormSecurityToken('openidconnect_unlink'),
            '$title' => DI::l10n()->t('OpenID Connect'),
            '$link_text' => DI::l10n()->t('Link OpenID Connect Account'),
            '$unlink_text' => DI::l10n()->t('Unlink Account'),
            '$confirm_json' => $confirmJson,
            '$description' => DI::l10n()->t('Link your local account with an OpenID Connect provider to use SSO for login.'),
        ]);
    }
}
