<?php

/**
 * Name: 13ft Replace
 * Description: Replaces occurrences of specified URLs configured by the user with the address of an alternative 13ft server configured by the admin.
 * Version: 1.0
 * Author: Dr. Tobias Quathamer <https://social.anoxinon.de/@toddy>
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 * Maintainer: Matthias Ebers <https://loma.ml/profile/feb>
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function ft_replace_install()
{
    Hook::register('prepare_body_final', 'addon/ft_replace/ft_replace.php', 'ft_replace_render');
    Hook::register('addon_settings', __FILE__, 'ft_replace_settings');
    Hook::register('addon_settings_post', __FILE__, 'ft_replace_settings_post');
}

function ft_replace_addon_admin_post()
{
    $server = trim($_POST['thirteenft_server'] ?? '');
    if (!empty($server)) {
        $server = rtrim(strtolower($server), '/');
        if (substr($server, 0, 4) !== 'http') {
            $server = 'https://' . $server;
        }
    }
    DI::config()->set('ft_replace', 'thirteenft_server', $server);
}

function ft_replace_addon_admin(string &$o)
{
    $thirteenft_server = DI::config()->get('ft_replace', 'thirteenft_server') ?? '';

    $t = Renderer::getMarkupTemplate('admin.tpl', 'addon/ft_replace/');
    $o = Renderer::replaceMacros($t, [
        '$thirteenft_server' => [
            'thirteenft_server',
            DI::l10n()->t('Global 13ft Instance URL'),
            $thirteenft_server,
            DI::l10n()->t('The URL of the 13ft instance used node-wide (e.g., https://13ft.example.com)'),
            null,
            'required'
        ],
        '$submit' => DI::l10n()->t('Save settings'),
    ]);
}

function ft_replace_settings(array &$data)
{
    $userId = DI::userSession()->getLocalUserId();
    if (!$userId) {
        return;
    }

    $enabled = DI::pConfig()->get($userId, 'ft_replace', 'enabled', false);
    $user_sites_array = DI::pConfig()->get($userId, 'ft_replace', 'user_sites', []) ?? [];
    $user_sites = implode(PHP_EOL, $user_sites_array);

    $t = Renderer::getMarkupTemplate('settings.tpl', 'addon/ft_replace/');

    $html = Renderer::replaceMacros($t, [
        '$enabled' => [
            'enabled',
            DI::l10n()->t('Enable 13ft Replace Addon'),
            $enabled,
            DI::l10n()->t('If enabled, your specified URLs below will be bypassed using the node\'s 13ft instance.')
        ],
        '$user_sites' => [
            'user_sites',
            DI::l10n()->t('Your personal Paywall Sites'),
            $user_sites,
            DI::l10n()->t('Specify the target URLs with protocol (e.g. https://zeit.de), one per line.'),
            null,
            'rows="6"'
        ],
        '$submit' => DI::l10n()->t('Save Settings'),
    ]);

    $data = [
        'addon' => 'ft_replace',
        'title' => DI::l10n()->t('13ft Replace Settings'),
        'html'  => $html,
    ];
}

function ft_replace_settings_post(array &$b)
{
    $userId = DI::userSession()->getLocalUserId();
    if (!$userId || empty($_POST['ft_replace-submit'])) {
        return;
    }

    DI::pConfig()->set($userId, 'ft_replace', 'enabled', (bool)$_POST['enabled']);

    $user_sites = explode(PHP_EOL, $_POST['user_sites'] ?? '');
    $user_sites = array_map(fn($value): string => rtrim(trim(strtolower($value)), '/'), $user_sites);
    $user_sites = array_filter($user_sites, fn($value): bool => !empty($value));
    $user_sites = array_unique($user_sites);

    $user_sites = array_map(
        fn($value): string => substr($value, 0, 4) !== 'http' ? 'https://' . $value : $value,
        $user_sites
    );
    asort($user_sites);

    DI::pConfig()->set($userId, 'ft_replace', 'user_sites', $user_sites);
}

function ft_replace_render(array &$b)
{
    $userId = DI::userSession()->getLocalUserId();
    if (!$userId || !DI::pConfig()->get($userId, 'ft_replace', 'enabled')) {
        return;
    }

    $server = DI::config()->get('ft_replace', 'thirteenft_server') ?? '';

    if (empty($server)) {
        return;
    }

    $user_sites = DI::pConfig()->get($userId, 'ft_replace', 'user_sites') ?? [] ?: [];
    $replaced = false;

    foreach ($user_sites as $site) {
        if (strpos($b['html'], $site) !== false) {
            $b['html'] = str_replace($site, $server . '/' . $site, $b['html']);
            $replaced = true;
        }
    }

    if ($replaced) {
        $b['html'] .= '<hr><p><small>' . DI::l10n()->t('(13ft replace addon active for some of your configured news sites.)') . '</small></p>';
    }
}
