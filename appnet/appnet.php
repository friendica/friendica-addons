<?php

/**
 * Name: App.net Connector
 * Description: Post to app.net
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function appnet_install() {
	register_hook('post_local',           'addon/appnet/appnet.php', 'appnet_post_local');
	register_hook('notifier_normal',      'addon/appnet/appnet.php', 'appnet_send');
	register_hook('jot_networks',         'addon/appnet/appnet.php', 'appnet_jot_nets');
	register_hook('connector_settings',      'addon/appnet/appnet.php', 'appnet_settings');
	register_hook('connector_settings_post', 'addon/appnet/appnet.php', 'appnet_settings_post');
}


function appnet_uninstall() {
	unregister_hook('post_local',       'addon/appnet/appnet.php', 'appnet_post_local');
	unregister_hook('notifier_normal',  'addon/appnet/appnet.php', 'appnet_send');
	unregister_hook('jot_networks',     'addon/appnet/appnet.php', 'appnet_jot_nets');
	unregister_hook('connector_settings',      'addon/appnet/appnet.php', 'appnet_settings');
	unregister_hook('connector_settings_post', 'addon/appnet/appnet.php', 'appnet_settings_post');
}

function appnet_module() {}

function appnet_content(&$a) {
        if(! local_user()) {
                notice( t('Permission denied.') . EOL);
                return '';
        }

        require_once("mod/settings.php");
        settings_init($a);

        if (isset($a->argv[1]))
                switch ($a->argv[1]) {
                        case "connect":
                                $o = appnet_connect($a);
                                break;
                        default:
                                $o = print_r($a->argv, true);
                                break;
                }
        else
		$o = appnet_connect($a);

        return $o;
}

function appnet_connect(&$a) {
	require_once 'addon/appnet/AppDotNet.php';

	$clientId     = get_pconfig(local_user(),'appnet','clientid');
	$clientSecret = get_pconfig(local_user(),'appnet','clientsecret');

	$app = new AppDotNet($clientId, $clientSecret);

	try {
		$token = $app->getAccessToken($a->get_baseurl().'/appnet/connect');

		logger("appnet_connect: authenticated");
		$o .= t("You are now authenticated to app.net. ");
		set_pconfig(local_user(),'appnet','token', $token);
	}
	catch (AppDotNetException $e) {
		$o .= t("<p>Error fetching token. Please try again.</p>");
	}

	$o .= '<br /><a href="'.$a->get_baseurl().'/settings/connectors">'.t("return to the connector page").'</a>';

	return($o);
}

function appnet_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$post = get_pconfig(local_user(),'appnet','post');
	if(intval($post) == 1) {
		$defpost = get_pconfig(local_user(),'appnet','post_by_default');
		$selected = ((intval($defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="appnet_enable"' . $selected . ' value="1" /> '
			. t('Post to app.net') . '</div>';
    }
}

function appnet_settings(&$a,&$s) {
	require_once 'addon/appnet/AppDotNet.php';

	if(! local_user())
		return;

	$token = get_pconfig(local_user(),'appnet','token');
	$app_clientId     = get_pconfig(local_user(),'appnet','clientid');
	$app_clientSecret = get_pconfig(local_user(),'appnet','clientsecret');

	/* Add our stylesheet to the page so we can make our settings look nice */
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/appnet/appnet.css' . '" media="all" />' . "\r\n";

	$enabled = get_pconfig(local_user(),'appnet','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = get_pconfig(local_user(),'appnet','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$s .= '<span id="settings_appnet_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_appnet_expanded\'); openClose(\'settings_appnet_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/appnet.png" /><h3 class="connector">'. t('App.net Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_appnet_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_appnet_expanded\'); openClose(\'settings_appnet_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/appnet.png" /><h3 class="connector">'. t('App.net Export').'</h3>';
	$s .= '</span>';

	if ($token != "") {
		$app = new AppDotNet($app_clientId, $app_clientSecret);
		$app->setAccessToken($token);

		try {
			$userdata = $app->getUser();

			$s .= '<div id="appnet-info" ><img id="appnet-avatar" src="'.$userdata["avatar_image"]["url"].'" /><p id="appnet-info-block">'. t('Currently connected to: ') .'<a href="'.$userdata["canonical_url"].'" target="_appnet">'.$userdata["username"].'</a><br /><em>'.$userdata["description"]["text"].'</em></p></div>';
			$s .= '<div id="appnet-enable-wrapper">';
			$s .= '<label id="appnet-enable-label" for="appnet-checkbox">' . t('Enable App.net Post Plugin') . '</label>';
			$s .= '<input id="appnet-checkbox" type="checkbox" name="appnet" value="1" ' . $checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="appnet-bydefault-wrapper">';
			$s .= '<label id="appnet-bydefault-label" for="appnet-bydefault">' . t('Post to App.net by default') . '</label>';
			$s .= '<input id="appnet-bydefault" type="checkbox" name="appnet_bydefault" value="1" ' . $def_checked . '/>';
			$s .= '</div><div class="clear"></div>';
		}
                catch (AppDotNetException $e) {
			$s .= t("<p>Error fetching user profile. Please clear the configuration and try again.</p>");
		}
		//$s .= print_r($userdata, true);

	} elseif (($app_clientId == '') OR ($app_clientSecret == '')) {
		$s .= t("<p>You have two ways to connect to App.net.</p>");
		$s .= "<hr />";
		$s .= t('<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. ');
		$s .= sprintf(t("Use '%s' as Redirect URI<p>"), $a->get_baseurl().'/appnet/connect');
		$s .= '<div id="appnet-clientid-wrapper">';
		$s .= '<label id="appnet-clientid-label" for="appnet-clientid">' . t('Client ID') . '</label>';
		$s .= '<input id="appnet-clientid" type="text" name="clientid" value="'.$app_clientId.'" />';
		$s .= '</div><div class="clear"></div>';
		$s .= '<div id="appnet-clientsecret-wrapper">';
		$s .= '<label id="appnet-clientsecret-label" for="appnet-clientsecret">' . t('Client Secret') . '</label>';
		$s .= '<input id="appnet-clientsecret" type="text" name="clientsecret" value="'.$app_clientSecret.'" />';
		$s .= '</div><div class="clear"></div>';
		$s .= "<hr />";
		$s .= t('<p>Second way: fetch a token at <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. ');
		$s .= t("Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>");
		$s .= '<div id="appnet-token-wrapper">';
		$s .= '<label id="appnet-token-label" for="appnet-token">' . t('Token') . '</label>';
		$s .= '<input id="appnet-token" type="text" name="token" value="'.$token.'" />';
		$s .= '</div><div class="clear"></div>';

	} else {
		$app = new AppDotNet($app_clientId, $app_clientSecret);

		$scope =  array('basic', 'stream', 'write_post',
				'public_messages', 'messages');

		$url = $app->getAuthUrl($a->get_baseurl().'/appnet/connect', $scope);
		$s .= '<div class="clear"></div>';
		$s .= '<a href="'.$url.'">'.t("Sign in using App.net").'</a>';
	}

	if (($app_clientId != '') OR ($app_clientSecret != '') OR ($token !='')) {
		$s .= '<div id="appnet-disconnect-wrapper">';
		$s .= '<label id="appnet-disconnect-label" for="appnet-disconnect">'. t('Clear OAuth configuration') .'</label>';

		$s .= '<input id="appnet-disconnect" type="checkbox" name="appnet-disconnect" value="1" />';
		$s .= '</div><div class="clear"></div>';
	}

	/* provide a submit button */
	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="appnet-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';

	$s .= '</div>';
}

function appnet_settings_post(&$a,&$b) {

	if(x($_POST,'appnet-submit')) {

		if (isset($_POST['appnet-disconnect'])) {
			del_pconfig(local_user(), 'appnet', 'clientsecret');
			del_pconfig(local_user(), 'appnet', 'clientid');
			del_pconfig(local_user(), 'appnet', 'token');
		}

		if (isset($_POST["clientsecret"]))
			set_pconfig(local_user(),'appnet','clientsecret', $_POST['clientsecret']);

		if (isset($_POST["clientid"]))
			set_pconfig(local_user(),'appnet','clientid', $_POST['clientid']);

		if (isset($_POST["token"]) AND ($_POST["token"] != ""))
			set_pconfig(local_user(),'appnet','token', $_POST['token']);

		set_pconfig(local_user(),'appnet','post',intval($_POST['appnet']));
		set_pconfig(local_user(),'appnet','post_by_default',intval($_POST['appnet_bydefault']));
	}
}

function appnet_post_local(&$a,&$b) {

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

	$post   = intval(get_pconfig(local_user(),'appnet','post'));

	$enable = (($post && x($_REQUEST,'appnet_enable')) ? intval($_REQUEST['appnet_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'appnet','post_by_default')))
		$enable = 1;

	if(!$enable)
		return;

	if(strlen($b['postopts']))
		$b['postopts'] .= ',';

	$b['postopts'] .= 'appnet';
}

function appnet_send(&$a,&$b) {

	logger('appnet_send: invoked for post '.$b['id']." ".$b['app']);

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
		return;

	if(! strstr($b['postopts'],'appnet'))
		return;

	if($b['parent'] != $b['id'])
		return;

	$token = get_pconfig($b['uid'],'appnet','token');

	if($token) {
		require_once 'addon/appnet/AppDotNet.php';

		$clientId     = get_pconfig(local_user(),'appnet','clientid');
		$clientSecret = get_pconfig(local_user(),'appnet','clientsecret');

		$app = new AppDotNet($clientId, $clientSecret);
		$app->setAccessToken($token);

		$data = array();

		require_once("include/plaintext.php");
		require_once("include/network.php");

		$post = plaintext($a, $b, 256, false);
		logger("appnet_send: converted message ".$b["id"]." result: ".print_r($post, true), LOGGER_DEBUG);

		if (isset($post["image"])) {
			$img_str = fetch_url($post['image'],true, $redirects, 10);
			$tempfile = tempnam(get_config("system","temppath"), "cache");
			file_put_contents($tempfile, $img_str);

			try {
				$photoFile = $app->createFile($tempfile, array(type => "com.github.jdolitsky.appdotnetphp.photo"));

				$data["annotations"][] = array(
								"type" => "net.app.core.oembed",
								"value" => array(
									"+net.app.core.file" => array(
										"file_id" => $photoFile["id"],
										"file_token" => $photoFile["file_token"],
										"format" => "oembed")
									)
								);
			}
			catch (AppDotNetException $e) {
				logger("appnet_send: Error creating file");
			}

			unlink($tempfile);
		}

		// To-Do
		// Alle Links verkürzen

		if (isset($post["url"]) AND !isset($post["title"])) {
			$display_url = str_replace(array("http://www.", "https://www."), array("", ""), $post["url"]);
			$display_url = str_replace(array("http://", "https://"), array("", ""), $display_url);

			if (strlen($display_url) > 26)
				$display_url = substr($display_url, 0, 25)."…";

			$post["title"] = $display_url;
		}

		if (isset($post["url"]) AND isset($post["title"])) {
			$post["title"] = shortenmsg($post["title"], 90);
			$post["text"] = shortenmsg($post["text"], 256 - strlen($post["title"]));
			$post["text"] .= "\n[".$post["title"]."](".$post["url"].")";
		} elseif (isset($post["url"])) {
			$post["url"] = short_link($post["url"]);
			$post["text"] = shortenmsg($post["text"], 240);
			$post["text"] .= " ".$post["url"];
		}

		//print_r($post);
		$data["entities"]["parse_links"] = true;
                $data["entities"]["parse_markdown_links"] = true;

		try {
			$ret = $app->createPost($post["text"], $data);
			logger("appnet_send: send message ".$b["id"]." result: ".print_r($ret, true), LOGGER_DEBUG);
		}
		catch (AppDotNetException $e) {
			logger("appnet_send: Error sending message ".$b["id"]);
		}
	}
}
