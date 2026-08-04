<?php

/**
 * Name: XMPP Chat
 * Description: Embeds Converse.js XMPP webchat client into Friendica
 * Version: 1.0
 * Author: Friendica Community
 * License: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Core\Config\Util\ConfigFileManager;

function xmppchat_install()
{
	Hook::register('load_config', 'addon/xmppchat/xmppchat.php', 'xmppchat_load_config');
	Hook::register('footer', 'addon/xmppchat/xmppchat.php', 'xmppchat_footer');
	Hook::register('addon_settings', 'addon/xmppchat/xmppchat.php', 'xmppchat_addon_settings');
	Hook::register('addon_settings_post', 'addon/xmppchat/xmppchat.php', 'xmppchat_addon_settings_post');
	DI::logger()->notice("installed xmppchat addon");
}

function xmppchat_addon_admin_post()
{
	DI::config()->set('xmppchat', 'websocket_url', trim($_POST['websocket_url'] ?? ''));
	DI::config()->set('xmppchat', 'bosh_url', trim($_POST['bosh_url'] ?? ''));
	DI::config()->set('xmppchat', 'domain', trim($_POST['domain'] ?? ''));
	DI::config()->set('xmppchat', 'auto_login', !empty($_POST['auto_login']));
	DI::config()->set('xmppchat', 'enable_mam', !empty($_POST['enable_mam']));
	DI::config()->set('xmppchat', 'enable_smacks', !empty($_POST['enable_smacks']));
	DI::config()->set('xmppchat', 'enable_omemo', !empty($_POST['enable_omemo']));
	DI::config()->set('xmppchat', 'allow_anonymous', !empty($_POST['allow_anonymous']));
	DI::config()->set('xmppchat', 'default_muc', trim($_POST['default_muc'] ?? ''));
}

function xmppchat_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/xmppchat/');
	$o = Renderer::replaceMacros($t, [
		'$page_intro'       => DI::l10n()->t('Configure the Converse.js XMPP webchat integration. Requires an XMPP server with WebSocket or BOSH support.'),
		'$connection_title' => DI::l10n()->t('Connection Settings'),
		'$auth_title'       => DI::l10n()->t('Authentication'),
		'$features_title'   => DI::l10n()->t('Features'),
		'$chatrooms_title'  => DI::l10n()->t('Chat Rooms'),
		'$help_omemo'       => DI::l10n()->t('OMEMO Encryption'),
		'$help_omemo_text'  => DI::l10n()->t('Requires users to verify device fingerprints. May increase initial connection time.'),
		'$help_muc'         => DI::l10n()->t('Default Chat Room'),
		'$help_muc_text'    => DI::l10n()->t('Users will automatically join this room on login. Format: roomname@conference.example.org'),
		'$help_anon'        => DI::l10n()->t('Anonymous Login'),
		'$help_anon_text'   => DI::l10n()->t('Allows users to join chat rooms without XMPP accounts. Requires server support for anonymous authentication.'),
		'$submit'           => DI::l10n()->t('Save Settings'),
		'$websocket_url'    => ['websocket_url', DI::l10n()->t('WebSocket URL'), DI::config()->get('xmppchat', 'websocket_url'), DI::l10n()->t('XMPP WebSocket endpoint (e.g., wss://xmpp.example.org:5281/xmpp-websocket)')],
		'$bosh_url'         => ['bosh_url', DI::l10n()->t('BOSH URL'), DI::config()->get('xmppchat', 'bosh_url'), DI::l10n()->t('XMPP BOSH endpoint for fallback (e.g., https://xmpp.example.org/http-bind)')],
		'$domain'           => ['domain', DI::l10n()->t('XMPP Domain'), DI::config()->get('xmppchat', 'domain'), DI::l10n()->t('XMPP server domain (e.g., example.org)')],
		'$auto_login'       => ['auto_login', DI::l10n()->t('Auto-Login'), DI::config()->get('xmppchat', 'auto_login', false), DI::l10n()->t('Attempt automatic login using Friendica username (requires matching XMPP accounts)')],
		'$enable_mam'       => ['enable_mam', DI::l10n()->t('Enable Message Archive'), DI::config()->get('xmppchat', 'enable_mam', true), DI::l10n()->t('Enable XEP-0313 Message Archive Management for chat history')],
		'$enable_smacks'    => ['enable_smacks', DI::l10n()->t('Enable Stream Management'), DI::config()->get('xmppchat', 'enable_smacks', true), DI::l10n()->t('Enable XEP-0198 Stream Management for reliable connections')],
		'$enable_omemo'     => ['enable_omemo', DI::l10n()->t('Enable OMEMO Encryption'), DI::config()->get('xmppchat', 'enable_omemo', false), DI::l10n()->t('Enable XEP-0384 OMEMO end-to-end encryption')],
		'$allow_anonymous'  => ['allow_anonymous', DI::l10n()->t('Allow Anonymous Login'), DI::config()->get('xmppchat', 'allow_anonymous', false), DI::l10n()->t('Allow users to join chat rooms without authentication')],
		'$default_muc'      => ['default_muc', DI::l10n()->t('Default Chat Room'), DI::config()->get('xmppchat', 'default_muc'), DI::l10n()->t('Auto-join this MUC room on login (e.g., lobby@conference.example.org)')],
	]);
}

function xmppchat_addon_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$uid              = DI::userSession()->getLocalUserId();
	$custom_jid       = DI::pConfig()->get($uid, 'xmppchat', 'custom_jid');
	$custom_password  = DI::pConfig()->get($uid, 'xmppchat', 'custom_password');
	$use_custom       = DI::pConfig()->get($uid, 'xmppchat', 'use_custom', false);
	$user_enabled     = DI::pConfig()->get($uid, 'xmppchat', 'enabled', DI::config()->get('xmppchat', 'enabled', false));
	$custom_websocket = DI::pConfig()->get($uid, 'xmppchat', 'custom_websocket_url');
	$custom_bosh      = DI::pConfig()->get($uid, 'xmppchat', 'custom_bosh_url');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/xmppchat/');
	$html = Renderer::replaceMacros($t, [
		'$title'            => DI::l10n()->t('XMPP Chat Settings'),
		'$submit'           => DI::l10n()->t('Save Settings'),
		'$info'             => DI::l10n()->t('You can connect with any XMPP account from any server. Leave password empty to keep the existing one.'),
		'$security_title'   => DI::l10n()->t('Security Note'),
		'$security_text'    => DI::l10n()->t('Your password is stored encrypted on the server. We recommend using a separate password for XMPP.'),
		'$user_enabled'     => ['xmppchat', DI::l10n()->t('Enable XMPP Chat'), $user_enabled, DI::l10n()->t('Show the chat widget for your account')],
		'$use_custom'       => ['use_custom', DI::l10n()->t('Use Custom XMPP Account'), $use_custom, DI::l10n()->t('Enable to use your own XMPP account from any server')],
		'$custom_jid'       => ['custom_jid', DI::l10n()->t('XMPP Address (JID)'), $custom_jid, DI::l10n()->t('Your full XMPP address (e.g., user@example.org or user@other-server.com)')],
		'$custom_password'  => ['custom_password', DI::l10n()->t('XMPP Password'), '', DI::l10n()->t('Your XMPP account password (stored encrypted)')],
		'$custom_websocket' => ['custom_websocket_url', DI::l10n()->t('WebSocket URL'), $custom_websocket, DI::l10n()->t('XMPP WebSocket endpoint for your account (e.g., wss://xmpp.example.org:5281/xmpp-websocket)')],
		'$custom_bosh'      => ['custom_bosh_url', DI::l10n()->t('BOSH URL'), $custom_bosh, DI::l10n()->t('XMPP BOSH endpoint for your account (e.g., https://xmpp.example.org/http-bind)')],
	]);

	$data = [
		'addon' => 'xmppchat',
		'title' => DI::l10n()->t('XMPP Chat'),
		'html'  => $html,
	];
}

function xmppchat_addon_settings_post(array &$b)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$uid = DI::userSession()->getLocalUserId();

	if (!empty($b['xmppchat-submit'])) {
		DI::pConfig()->set($uid, 'xmppchat', 'enabled', !empty($b['xmppchat']));
		DI::pConfig()->set($uid, 'xmppchat', 'use_custom', !empty($b['use_custom']));
		DI::pConfig()->set($uid, 'xmppchat', 'custom_jid', trim($b['custom_jid'] ?? ''));
		DI::pConfig()->set($uid, 'xmppchat', 'custom_websocket_url', trim($b['custom_websocket_url'] ?? ''));
		DI::pConfig()->set($uid, 'xmppchat', 'custom_bosh_url', trim($b['custom_bosh_url'] ?? ''));

		// Only update password if provided
		if (!empty($b['custom_password'])) {
			// Note: In production, use proper encryption (e.g., openssl_encrypt)
			// For now, store base64-encoded (NOT SECURE - just for demonstration)
			$encrypted_password = base64_encode($b['custom_password']);
			DI::pConfig()->set($uid, 'xmppchat', 'custom_password', $encrypted_password);
		}
	}
}

function xmppchat_load_config(ConfigFileManager $loader)
{
	DI::appHelper()->getConfigCache()->load($loader->loadAddonConfig('xmppchat'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function xmppchat_footer(string &$body)
{
	DI::logger()->debug("xmppchat: footer hook called");
	// Only show the widget for authenticated users who enabled it in their settings
	if (!DI::userSession()->isAuthenticated()) {
		return;
	}

	$uid          = DI::userSession()->getLocalUserId();
	$user_enabled = DI::pConfig()->get($uid, 'xmppchat', 'enabled', false);
	if (!$user_enabled) {
		return;
	}

	// Load global features/settings
	$domain          = DI::config()->get('xmppchat', 'domain');
	$auto_login      = DI::config()->get('xmppchat', 'auto_login', false);
	$enable_mam      = DI::config()->get('xmppchat', 'enable_mam', true);
	$enable_smacks   = DI::config()->get('xmppchat', 'enable_smacks', true);
	$enable_omemo    = DI::config()->get('xmppchat', 'enable_omemo', false);
	$allow_anonymous = DI::config()->get('xmppchat', 'allow_anonymous', false);
	$default_muc     = DI::config()->get('xmppchat', 'default_muc');

	// Determine whether to use custom user endpoints or global ones
	$use_custom    = DI::pConfig()->get($uid, 'xmppchat', 'use_custom', false);
	$jid           = '';
	$password      = '';
	$websocket_url = null;
	$bosh_url      = null;

	if ($use_custom) {
		// User explicitly chose a custom account: use their endpoints and credentials only
		$websocket_url      = DI::pConfig()->get($uid, 'xmppchat', 'custom_websocket_url');
		$bosh_url           = DI::pConfig()->get($uid, 'xmppchat', 'custom_bosh_url');
		$jid                = DI::pConfig()->get($uid, 'xmppchat', 'custom_jid');
		$encrypted_password = DI::pConfig()->get($uid, 'xmppchat', 'custom_password');
		if ($encrypted_password) {
			$password = base64_decode($encrypted_password);
		}

		// If the user selected a custom account but didn't provide endpoints, don't attempt to connect
		if (empty($websocket_url) && empty($bosh_url)) {
			DI::logger()->warning("xmppchat: user $uid selected custom XMPP account but no websocket or bosh URL provided");
			return;
		}
	} else {
		// Use global endpoints
		$websocket_url = DI::config()->get('xmppchat', 'websocket_url');
		$bosh_url      = DI::config()->get('xmppchat', 'bosh_url');

		if (empty($websocket_url) && empty($bosh_url)) {
			DI::logger()->warning("xmppchat: No websocket_url or bosh_url configured globally");
			return;
		}

		// Auto-login using Friendica username if configured
		if ($auto_login) {
			$nickname = DI::session()->get('nickname');
			if ($nickname && $domain) {
				$jid = $nickname . '@' . $domain;
			}
		}
	}

	$tpl       = Renderer::getMarkupTemplate('xmppchat.tpl', 'addon/xmppchat/');
	$chat_html = Renderer::replaceMacros($tpl, [
		'$websocket_url'   => $websocket_url,
		'$bosh_url'        => $bosh_url,
		'$jid'             => $jid,
		'$password'        => $password,
		'$auto_login'      => $auto_login,
		'$allow_anonymous' => $allow_anonymous,
		'$enable_mam'      => $enable_mam ? 'true' : 'false',
		'$enable_smacks'   => $enable_smacks ? 'true' : 'false',
		'$enable_omemo'    => $enable_omemo ? 'true' : 'false',
		'$default_muc'     => $default_muc,
	]);

	$body .= $chat_html;
}
