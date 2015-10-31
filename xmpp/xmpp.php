<?php
/**
 * Name: XMPP (Jabber)
 * Description: Embedded XMPP (Jabber) client
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function xmpp_install() {
	register_hook('page_end', 'addon/xmpp/xmpp.php', 'xmpp_script');
	register_hook('logged_in', 'addon/xmpp/xmpp.php', 'xmpp_login');
}

function xmpp_uninstall() {
	unregister_hook('page_end', 'addon/xmpp/xmpp.php', 'xmpp_script');
	unregister_hook('logged_in', 'addon/xmpp/xmpp.php', 'xmpp_login');
}

function xmpp_login($a,$b) {
	if (!$_SESSION["allow_api"]) {
		$password = substr(random_string(),0,16);
		set_pconfig(local_user(), "xmpp", "password", $password);
	}
}

function xmpp_plugin_admin(&$a, &$o){
        $t = get_markup_template("admin.tpl", "addon/xmpp/");

        $o = replace_macros($t, array(
                '$submit' => t('Save Settings'),
                '$bosh_proxy'       => array('bosh_proxy', t('BOSH proxy'),            get_config('xmpp', 'bosh_proxy'), ''),
                '$central_userbase' => array('central_userbase', t('Use central userbase'), get_config('xmpp', 'central_userbase'), t('If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.')),
        ));
}

function xmpp_plugin_admin_post(&$a){
        $bosh_proxy       = ((x($_POST,'bosh_proxy')) ?       trim($_POST['bosh_proxy']) : '');
        $central_userbase = ((x($_POST,'central_userbase')) ? intval($_POST['central_userbase']) : false);
        set_config('xmpp','bosh_proxy',$bosh_proxy);
        set_config('xmpp','central_userbase',$central_userbase);
        info( t('Settings updated.'). EOL );
}

function xmpp_script(&$a,&$s) {
	xmpp_converse($a,$s);
	//xmpp_jappix($a,$s);
}

function xmpp_converse(&$a,&$s) {
	if (!local_user())
		return;

	if ($_GET["mode"] == "minimal")
		return;

	$a->page['htmlhead'] .= '<link type="text/css" rel="stylesheet" media="screen" href="addon/xmpp/converse/css/converse.css" />'."\n";
	$a->page['htmlhead'] .= '<script src="addon/xmpp/converse/builds/converse.min.js"></script>'."\n";

	$bosh_proxy = get_config("xmpp", "bosh_proxy");

	if (get_config("xmpp", "central_userbase")) {
		$password = get_pconfig(local_user(), "xmpp", "password");

		if ($password == "") {
			$password = substr(random_string(),0,16);
			set_pconfig(local_user(), "xmpp", "password", $password);
		}

		$jid = $a->user["nickname"]."@".$a->get_hostname()."/converse-".substr(random_string(),0,5);;

		$auto_login = "auto_login: true,
			authentication: 'login',
			jid: '$jid',
			password: '$password',
			allow_logout: true,
			auto_list_rooms: true,";
	} else
		$auto_login = "";

	if (in_array($a->argv[0], array("manage", "logout")))
		$additional_commands = "converse.user.logout();\n";
	else
		$additional_commands = "";

	$on_ready = "";
	//$on_ready = "converse.rooms.open(['support@conference.pirati.ca']);\n";

//  converse.contacts.add('ike@jabber.piratenpartei.de');

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

function xmpp_jappix(&$a,&$s) {
	if (!local_user())
		return;

	if ($_GET["mode"] == "minimal")
		return;

	$bosh_proxy = get_config("xmpp", "bosh_proxy");

	if (get_config("xmpp", "central_userbase")) {
		$password = get_pconfig(local_user(), "xmpp", "password");

		if ($password == "") {
			$password = substr(random_string(),0,16);
			set_pconfig(local_user(), "xmpp", "password", $password);
		}

		$user = $a->user["nickname"];
		$domain = $a->get_hostname();

		$auto_login = "auto_login: true,
			authentication: 'login',
			jid: '$jid',
			password: '$password',
			allow_logout: false,";
	} else
		$auto_login = "";

	//$a->page['htmlhead'] .= "<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>";

	$a->page['htmlhead'] .= "<script type='text/javascript' src='addon/xmpp/jappix/javascripts/mini.js'></script>
					<script type='text/javascript'>
						jQuery(document).ready(function() {
							JAPPIX_STATIC = 'addon/xmpp/jappix/';
							HOST_BOSH = '$bosh_proxy'

							JappixMini.launch({
						           connection: {
						             user: '$user',
						             password: '$password',
						             domain: '$domain'
						           },
						           application: {
						             network: {
						               autoconnect: true
						             },
						             interface: {
						               showpane: false
						             }
						           }
						        });
						});
				</script>";
}

?>
