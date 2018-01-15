<?php

/**
 * Name: XMPP (Jabber)
 * Description: Embedded XMPP (Jabber) client
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */
use Friendica\App;
use Friendica\Core\Config;
use Friendica\Core\PConfig;

function xmpp_install()
{
	register_hook('plugin_settings', 'addon/xmpp/xmpp.php', 'xmpp_plugin_settings');
	register_hook('plugin_settings_post', 'addon/xmpp/xmpp.php', 'xmpp_plugin_settings_post');
	register_hook('page_end', 'addon/xmpp/xmpp.php', 'xmpp_script');
	register_hook('logged_in', 'addon/xmpp/xmpp.php', 'xmpp_login');
}

function xmpp_uninstall()
{
	unregister_hook('plugin_settings', 'addon/xmpp/xmpp.php', 'xmpp_plugin_settings');
	unregister_hook('plugin_settings_post', 'addon/xmpp/xmpp.php', 'xmpp_plugin_settings_post');
	unregister_hook('page_end', 'addon/xmpp/xmpp.php', 'xmpp_script');
	unregister_hook('logged_in', 'addon/xmpp/xmpp.php', 'xmpp_login');
}

function xmpp_plugin_settings_post()
{
	if (!local_user() || (!x($_POST, 'xmpp-settings-submit'))) {
		return;
	}
	PConfig::set(local_user(), 'xmpp', 'enabled', intval($_POST['xmpp_enabled']));
	PConfig::set(local_user(), 'xmpp', 'individual', intval($_POST['xmpp_individual']));
	PConfig::set(local_user(), 'xmpp', 'bosh_proxy', $_POST['xmpp_bosh_proxy']);

	info(t('XMPP settings updated.') . EOL);
}

function xmpp_plugin_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	/* Add our stylesheet to the xmpp so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/xmpp/xmpp.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = intval(PConfig::get(local_user(), 'xmpp', 'enabled'));
	$enabled_checked = (($enabled) ? ' checked="checked" ' : '');

	$individual = intval(PConfig::get(local_user(), 'xmpp', 'individual'));
	$individual_checked = (($individual) ? ' checked="checked" ' : '');

	$bosh_proxy = PConfig::get(local_user(), "xmpp", "bosh_proxy");

	/* Add some HTML to the existing form */
	$s .= '<span id="settings_xmpp_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_xmpp_expanded\'); openClose(\'settings_xmpp_inflated\');">';
	$s .= '<h3>' . t('XMPP-Chat (Jabber)') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_xmpp_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_xmpp_expanded\'); openClose(\'settings_xmpp_inflated\');">';
	$s .= '<h3>' . t('XMPP-Chat (Jabber)') . '</h3>';
	$s .= '</span>';

	$s .= '<div id="xmpp-settings-wrapper">';
	$s .= '<label id="xmpp-enabled-label" for="xmpp-enabled">' . t('Enable Webchat') . '</label>';
	$s .= '<input id="xmpp-enabled" type="checkbox" name="xmpp_enabled" value="1" ' . $enabled_checked . '/>';
	$s .= '<div class="clear"></div>';

	if (Config::get("xmpp", "central_userbase")) {
		$s .= '<label id="xmpp-individual-label" for="xmpp-individual">' . t('Individual Credentials') . '</label>';
		$s .= '<input id="xmpp-individual" type="checkbox" name="xmpp_individual" value="1" ' . $individual_checked . '/>';
		$s .= '<div class="clear"></div>';
	}

	if (!Config::get("xmpp", "central_userbase") || PConfig::get(local_user(), "xmpp", "individual")) {
		$s .= '<label id="xmpp-bosh-proxy-label" for="xmpp-bosh-proxy">' . t('Jabber BOSH host') . '</label>';
		$s .= ' <input id="xmpp-bosh-proxy" type="text" name="xmpp_bosh_proxy" value="' . $bosh_proxy . '" />';
		$s .= '<div class="clear"></div>';
	}

	$s .= '</div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="xmpp-settings-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';
}

function xmpp_login()
{
	if (!$_SESSION["allow_api"]) {
		$password = random_string(16);
		PConfig::set(local_user(), "xmpp", "password", $password);
	}
}

function xmpp_plugin_admin(App $a, &$o)
{
	$t = get_markup_template("admin.tpl", "addon/xmpp/");

	$o = replace_macros($t, [
		'$submit' => t('Save Settings'),
		'$bosh_proxy' => ['bosh_proxy', t('Jabber BOSH host'), Config::get('xmpp', 'bosh_proxy'), ''],
		'$central_userbase' => ['central_userbase', t('Use central userbase'), Config::get('xmpp', 'central_userbase'), t('If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.')],
	]);
}

function xmpp_plugin_admin_post()
{
	$bosh_proxy = ((x($_POST, 'bosh_proxy')) ? trim($_POST['bosh_proxy']) : '');
	$central_userbase = ((x($_POST, 'central_userbase')) ? intval($_POST['central_userbase']) : false);
	Config::set('xmpp', 'bosh_proxy', $bosh_proxy);
	Config::set('xmpp', 'central_userbase', $central_userbase);
	info(t('Settings updated.') . EOL);
}

function xmpp_script(App $a)
{
	xmpp_converse($a);
}

function xmpp_converse(App $a)
{
	if (!local_user()) {
		return;
	}

	if ($_GET["mode"] == "minimal") {
		return;
	}

	if ($a->is_mobile || $a->is_tablet) {
		return;
	}

	if (!PConfig::get(local_user(), "xmpp", "enabled")) {
		return;
	}

	if (in_array($a->query_string, ["admin/federation/"])) {
		return;
	}

	$a->page['htmlhead'] .= '<link type="text/css" rel="stylesheet" media="screen" href="addon/xmpp/converse/css/converse.css" />' . "\n";
	$a->page['htmlhead'] .= '<script src="addon/xmpp/converse/builds/converse.min.js"></script>' . "\n";

	if (Config::get("xmpp", "central_userbase") && !PConfig::get(local_user(), "xmpp", "individual")) {
		$bosh_proxy = Config::get("xmpp", "bosh_proxy");

		$password = PConfig::get(local_user(), "xmpp", "password", '', true);

		if ($password == "") {
			$password = random_string(16);
			PConfig::set(local_user(), "xmpp", "password", $password);
		}

		$jid = $a->user["nickname"] . "@" . $a->get_hostname() . "/converse-" . random_string(5);

		$auto_login = "auto_login: true,
			authentication: 'login',
			jid: '$jid',
			password: '$password',
			allow_logout: false,";
	} else {
		$bosh_proxy = PConfig::get(local_user(), "xmpp", "bosh_proxy");

		$auto_login = "";
	}

	if ($bosh_proxy == "") {
		return;
	}

	if (in_array($a->argv[0], ["manage", "logout"])) {
		$additional_commands = "converse.user.logout();\n";
	} else {
		$additional_commands = "";
	}

	$on_ready = "";

	$initialize = "converse.initialize({
					bosh_service_url: '$bosh_proxy',
					keepalive: true,
					message_carbons: false,
					forward_messages: false,
					play_sounds: true,
					sounds_path: 'addon/xmpp/converse/sounds/',
					roster_groups: false,
					show_controlbox_by_default: false,
					show_toolbar: true,
					allow_contact_removal: false,
					allow_registration: false,
					hide_offline_users: true,
					allow_chat_pending_contacts: false,
					allow_dragresize: true,
					auto_away: 0,
					auto_xa: 0,
					csi_waiting_time: 300,
					auto_reconnect: true,
					$auto_login
					xhr_user_search: false
				});\n";

	$a->page['htmlhead'] .= "<script>
					require(['converse'], function (converse) {
						$initialize
						converse.listen.on('ready', function (event) {
							$on_ready
						});
						$additional_commands
					});
				</script>";
}
