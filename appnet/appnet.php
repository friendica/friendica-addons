<?php

/**
 * Name: App.net Connector
 * Description: app.net postings import and export
 * Version: 0.2
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

/*
 To-Do:
 - Use embedded pictures for the attachment information (large attachment)
 - Sound links must be handled
 - https://alpha.app.net/sr_rolando/post/32365203 - double pictures
*/

define('APPNET_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function appnet_install() {
	register_hook('post_local',		'addon/appnet/appnet.php', 'appnet_post_local');
	register_hook('notifier_normal',	'addon/appnet/appnet.php', 'appnet_send');
	register_hook('jot_networks',		'addon/appnet/appnet.php', 'appnet_jot_nets');
	register_hook('cron',			'addon/appnet/appnet.php', 'appnet_cron');
	register_hook('connector_settings',	'addon/appnet/appnet.php', 'appnet_settings');
	register_hook('connector_settings_post','addon/appnet/appnet.php', 'appnet_settings_post');
}


function appnet_uninstall() {
	unregister_hook('post_local',       'addon/appnet/appnet.php', 'appnet_post_local');
	unregister_hook('notifier_normal',  'addon/appnet/appnet.php', 'appnet_send');
	unregister_hook('jot_networks',     'addon/appnet/appnet.php', 'appnet_jot_nets');
	unregister_hook('cron',			'addon/appnet/appnet.php', 'appnet_cron');
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

function appnet_plugin_admin(&$a, &$o){
        $t = get_markup_template( "admin.tpl", "addon/appnet/" );

        $o = replace_macros($t, array(
                '$submit' => t('Save Settings'),
                                                                // name, label, value, help, [extra values]
                '$clientid' => array('clientid', t('Client ID'),  get_config('appnet', 'clientid' ), ''),
                '$clientsecret' => array('clientsecret', t('Client Secret'),  get_config('appnet', 'clientsecret' ), ''),
        ));
}

function appnet_plugin_admin_post(&$a){
        $clientid     =       ((x($_POST,'clientid'))              ? notags(trim($_POST['clientid']))   : '');
        $clientsecret =       ((x($_POST,'clientsecret'))   ? notags(trim($_POST['clientsecret'])): '');
        set_config('appnet','clientid',$clientid);
        set_config('appnet','clientsecret',$clientsecret);
        info( t('Settings updated.'). EOL );
}

function appnet_connect(&$a) {
	require_once 'addon/appnet/AppDotNet.php';

	$clientId     = get_config('appnet','clientid');
	$clientSecret = get_config('appnet','clientsecret');

	if (($clientId == "") OR ($clientSecret == "")) {
		$clientId     = get_pconfig(local_user(),'appnet','clientid');
		$clientSecret = get_pconfig(local_user(),'appnet','clientsecret');
	}

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

	$app_clientId     = get_config('appnet','clientid');
	$app_clientSecret = get_config('appnet','clientsecret');

	if (($app_clientId == "") OR ($app_clientSecret == "")) {
		$app_clientId     = get_pconfig(local_user(),'appnet','clientid');
		$app_clientSecret = get_pconfig(local_user(),'appnet','clientsecret');
	}

	/* Add our stylesheet to the page so we can make our settings look nice */
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/appnet/appnet.css' . '" media="all" />' . "\r\n";

	$enabled = get_pconfig(local_user(),'appnet','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = get_pconfig(local_user(),'appnet','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$importenabled = get_pconfig(local_user(),'appnet','import');
	$importchecked = (($importenabled) ? ' checked="checked" ' : '');

	$ownid =  get_pconfig(local_user(),'appnet','ownid');

	$s .= '<span id="settings_appnet_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_appnet_expanded\'); openClose(\'settings_appnet_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/appnet.png" /><h3 class="connector">'. t('App.net Import/Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_appnet_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_appnet_expanded\'); openClose(\'settings_appnet_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/appnet.png" /><h3 class="connector">'. t('App.net Import/Export').'</h3>';
	$s .= '</span>';

	if ($token != "") {
		$app = new AppDotNet($app_clientId, $app_clientSecret);
		$app->setAccessToken($token);

		try {
			$userdata = $app->getUser();

			if ($ownid != $userdata["id"])
				set_pconfig(local_user(),'appnet','ownid', $userdata["id"]);

			$s .= '<div id="appnet-info" ><img id="appnet-avatar" src="'.$userdata["avatar_image"]["url"].'" /><p id="appnet-info-block">'. t('Currently connected to: ') .'<a href="'.$userdata["canonical_url"].'" target="_appnet">'.$userdata["username"].'</a><br /><em>'.$userdata["description"]["text"].'</em></p></div>';
			$s .= '<div id="appnet-enable-wrapper">';
			$s .= '<label id="appnet-enable-label" for="appnet-checkbox">' . t('Enable App.net Post Plugin') . '</label>';
			$s .= '<input id="appnet-checkbox" type="checkbox" name="appnet" value="1" ' . $checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="appnet-bydefault-wrapper">';
			$s .= '<label id="appnet-bydefault-label" for="appnet-bydefault">' . t('Post to App.net by default') . '</label>';
			$s .= '<input id="appnet-bydefault" type="checkbox" name="appnet_bydefault" value="1" ' . $def_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<label id="appnet-import-label" for="appnet-import">'.t('Import the remote timeline').'</label>';
			$s .= '<input id="appnet-import" type="checkbox" name="appnet_import" value="1" '. $importchecked . '/>';
			$s .= '<div class="clear"></div>';

		}
		catch (AppDotNetException $e) {
			$s .= t("<p>Error fetching user profile. Please clear the configuration and try again.</p>");
		}

	} elseif (($app_clientId == '') OR ($app_clientSecret == '')) {
		$s .= t("<p>You have two ways to connect to App.net.</p>");
		$s .= "<hr />";
		$s .= t('<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. ');
		$s .= sprintf(t("Use '%s' as Redirect URI<p>"), $a->get_baseurl().'/appnet/connect');
		$s .= '<div id="appnet-clientid-wrapper">';
		$s .= '<label id="appnet-clientid-label" for="appnet-clientid">' . t('Client ID') . '</label>';
		$s .= '<input id="appnet-clientid" type="text" name="clientid" value="" />';
		$s .= '</div><div class="clear"></div>';
		$s .= '<div id="appnet-clientsecret-wrapper">';
		$s .= '<label id="appnet-clientsecret-label" for="appnet-clientsecret">' . t('Client Secret') . '</label>';
		$s .= '<input id="appnet-clientsecret" type="text" name="clientsecret" value="" />';
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
			del_pconfig(local_user(), 'appnet', 'post');
			del_pconfig(local_user(), 'appnet', 'post_by_default');
			del_pconfig(local_user(), 'appnet', 'import');
		}

		if (isset($_POST["clientsecret"]))
			set_pconfig(local_user(),'appnet','clientsecret', $_POST['clientsecret']);

		if (isset($_POST["clientid"]))
			set_pconfig(local_user(),'appnet','clientid', $_POST['clientid']);

		if (isset($_POST["token"]) AND ($_POST["token"] != ""))
			set_pconfig(local_user(),'appnet','token', $_POST['token']);

		set_pconfig(local_user(), 'appnet', 'post', intval($_POST['appnet']));
		set_pconfig(local_user(), 'appnet', 'post_by_default', intval($_POST['appnet_bydefault']));
		set_pconfig(local_user(), 'appnet', 'import', intval($_POST['appnet_import']));
	}
}

function appnet_post_local(&$a,&$b) {
	if($b['edit'])
		return;

	if((local_user()) && (local_user() == $b['uid']) && (!$b['private']) && (!$b['parent'])) {
		$appnet_post = intval(get_pconfig(local_user(),'appnet','post'));
		$appnet_enable = (($appnet_post && x($_REQUEST,'appnet_enable')) ? intval($_REQUEST['appnet_enable']) : 0);

		// if API is used, default to the chosen settings
		if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'appnet','post_by_default')))
			$appnet_enable = 1;

		if(! $appnet_enable)
			return;

		if(strlen($b['postopts']))
			$b['postopts'] .= ',';

		$b['postopts'] .= 'appnet';
	}
}

function appnet_create_entities($a, $b, $postdata) {
	require_once("include/bbcode.php");
	require_once("include/plaintext.php");

	$bbcode = $b["body"];
	$bbcode = bb_remove_share_information($bbcode, false, true);

	// Change pure links in text to bbcode uris
	$bbcode = preg_replace("/([^\]\='".'"'."]|^)(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)/ism", '$1[url=$2]$2[/url]', $bbcode);

	$URLSearchString = "^\[\]";

	$bbcode = preg_replace("/#\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism",'#$2',$bbcode);
	$bbcode = preg_replace("/@\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism",'@$2',$bbcode);
	$bbcode = preg_replace("/\[bookmark\=([$URLSearchString]*)\](.*?)\[\/bookmark\]/ism",'[url=$1]$2[/url]',$bbcode);
	$bbcode = preg_replace("/\[video\](.*?)\[\/video\]/ism",'[url=$1]$1[/url]',$bbcode);
	$bbcode = preg_replace("/\[youtube\]https?:\/\/(.*?)\[\/youtube\]/ism",'[url=https://$1]https://$1[/url]',$bbcode);
	$bbcode = preg_replace("/\[youtube\]([A-Za-z0-9\-_=]+)(.*?)\[\/youtube\]/ism",
			       '[url=https://www.youtube.com/watch?v=$1]https://www.youtube.com/watch?v=$1[/url]', $bbcode);
	$bbcode = preg_replace("/\[vimeo\]https?:\/\/(.*?)\[\/vimeo\]/ism",'[url=https://$1]https://$1[/url]',$bbcode);
	$bbcode = preg_replace("/\[vimeo\]([0-9]+)(.*?)\[\/vimeo\]/ism",
				'[url=https://vimeo.com/$1]https://vimeo.com/$1[/url]', $bbcode);
	//$bbcode = preg_replace("/\[vimeo\](.*?)\[\/vimeo\]/ism",'[url=$1]$1[/url]',$bbcode);

	$bbcode = preg_replace("/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/ism", '[img]$3[/img]', $bbcode);


	preg_match_all("/\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism", $bbcode, $urls, PREG_SET_ORDER);

	$bbcode = preg_replace("/\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism",'$1',$bbcode);

	$b["body"] = $bbcode;

	// To-Do:
	// Bilder
	// https://alpha.app.net/heluecht/post/32424376
	// https://alpha.app.net/heluecht/post/32424307

	$plaintext = plaintext($a, $b, 0, false, 6);

	$text = $plaintext["text"];

	$start = 0;
	$entities = array();

	foreach ($urls AS $url) {
		$lenurl = iconv_strlen($url[1], "UTF-8");
		$len = iconv_strlen($url[2], "UTF-8");
		$pos = iconv_strpos($text, $url[1], $start, "UTF-8");
		$pre = iconv_substr($text, 0, $pos, "UTF-8");
		$post = iconv_substr($text, $pos + $lenurl, 1000000, "UTF-8");

		$mid = $url[2];
		$html = bbcode($mid, false, false, 6);
		$mid = html2plain($html, 0, true);

		$mid = trim(html_entity_decode($mid,ENT_QUOTES,'UTF-8'));

		$text = $pre.$mid.$post;

		if ($mid != "")
			$entities[] = array("pos" => $pos, "len" => $len, "url" => $url[1], "text" => $mid);

		$start = $pos + 1;
	}

	if (isset($postdata["url"]) AND isset($postdata["title"])) {
		$postdata["title"] = shortenmsg($postdata["title"], 90);
		$max = 256 - strlen($postdata["title"]);
		$text = shortenmsg($text, $max);
		$text .= "\n[".$postdata["title"]."](".$postdata["url"].")";
	} elseif (isset($postdata["url"])) {
		$postdata["url"] = short_link($postdata["url"]);
		$max = 240;
		$text = shortenmsg($text, $max);
		$text .= " [".$postdata["url"]."](".$postdata["url"].")";
	} else {
		$max = 256;
		$text = shortenmsg($text, $max);
	}

	if (iconv_strlen($text, "UTF-8") < $max)
		$max = iconv_strlen($text, "UTF-8");

	krsort($entities);
	foreach ($entities AS $entity) {
		//if (iconv_strlen($text, "UTF-8") >= $entity["pos"] + $entity["len"]) {
		if (($entity["pos"] + $entity["len"]) <= $max) {
			$pre = iconv_substr($text, 0, $entity["pos"], "UTF-8");
			$post = iconv_substr($text, $entity["pos"] + $entity["len"], 1000000, "UTF-8");

			$text = $pre."[".$entity["text"]."](".$entity["url"].")".$post;
		}
	}


	return($text);
}

function appnet_send(&$a,&$b) {

	logger('appnet_send: invoked for post '.$b['id']." ".$b['app']);

	if (!get_pconfig($b["uid"],'appnet','import')) {
		if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
			return;
	}

	if($b['parent'] != $b['id']) {
		logger("appnet_send: parameter ".print_r($b, true), LOGGER_DATA);

		// Looking if its a reply to an app.net post
		if ((substr($b["parent-uri"], 0, 5) != "adn::") AND (substr($b["extid"], 0, 5) != "adn::") AND (substr($b["thr-parent"], 0, 5) != "adn::")) {
			logger("appnet_send: no app.net post ".$b["parent"]);
			return;
		}

		$r = q("SELECT * FROM item WHERE item.uri = '%s' AND item.uid = %d LIMIT 1",
			dbesc($b["thr-parent"]),
			intval($b["uid"]));

		if(!count($r)) {
			logger("appnet_send: no parent found ".$b["thr-parent"]);
			return;
		} else {
			$iscomment = true;
			$orig_post = $r[0];
		}

		$nicknameplain = preg_replace("=https?://alpha.app.net/(.*)=ism", "$1", $orig_post["author-link"]);
		$nickname = "@[url=".$orig_post["author-link"]."]".$nicknameplain."[/url]";
		$nicknameplain = "@".$nicknameplain;

		logger("appnet_send: comparing ".$nickname." and ".$nicknameplain." with ".$b["body"], LOGGER_DEBUG);
		if ((strpos($b["body"], $nickname) === false) AND (strpos($b["body"], $nicknameplain) === false))
			$b["body"] = $nickname." ".$b["body"];

		logger("appnet_send: parent found ".print_r($orig_post, true), LOGGER_DATA);
	} else {
		$iscomment = false;

		if($b['private'] OR !strstr($b['postopts'],'appnet'))
			return;
	}

	if (($b['verb'] == ACTIVITY_POST) AND $b['deleted'])
		appnet_action($a, $b["uid"], substr($orig_post["uri"], 5), "delete");

	if($b['verb'] == ACTIVITY_LIKE) {
		logger("appnet_send: ".print_r($b, true), LOGGER_DEBUG);
		logger("appnet_send: parameter 2 ".substr($b["thr-parent"], 5), LOGGER_DEBUG);
		if ($b['deleted'])
			appnet_action($a, $b["uid"], substr($b["thr-parent"], 5), "unlike");
		else
			appnet_action($a, $b["uid"], substr($b["thr-parent"], 5), "like");
		return;
	}

	if($b['deleted'] || ($b['created'] !== $b['edited']))
		return;

	$token = get_pconfig($b['uid'],'appnet','token');

	if($token) {

		// If it's a repeated message from app.net then do a native repost and exit
		if (appnet_is_repost($a, $b['uid'], $b['body']))
			return;


		require_once 'addon/appnet/AppDotNet.php';

		$clientId     = get_pconfig($b["uid"],'appnet','clientid');
		$clientSecret = get_pconfig($b["uid"],'appnet','clientsecret');

		$app = new AppDotNet($clientId, $clientSecret);
		$app->setAccessToken($token);

		$data = array();

		require_once("include/plaintext.php");
		require_once("include/network.php");

		$post = plaintext($a, $b, 256, false, 6);
		logger("appnet_send: converted message ".$b["id"]." result: ".print_r($post, true), LOGGER_DEBUG);

		if (isset($post["image"])) {
			$img_str = fetch_url($post['image'],true, $redirects, 10);
			$tempfile = tempnam(get_temppath(), "cache");
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
				logger("appnet_send: Error creating file ".appnet_error($e->getMessage()));
			}

			unlink($tempfile);
		}

		// Adding a link to the original post, if it is a root post
		if($b['parent'] == $b['id'])
			$data["annotations"][] = array(
							"type" => "net.app.core.crosspost",
							"value" => array("canonical_url" => $b["plink"])
							);

		// Adding the original post
		$attached_data = get_attached_data($b["body"]);
		$attached_data["post-uri"] = $b["uri"];
		$attached_data["post-title"] = $b["title"];
		$attached_data["post-body"] = substr($b["body"], 0, 4000); // To-Do: Better shortening
		$attached_data["post-tag"] = $b["tag"];
		$attached_data["author-name"] = $b["author-name"];
		$attached_data["author-link"] = $b["author-link"];
		$attached_data["author-avatar"] = $b["author-avatar"];

		$data["annotations"][] = array(
						"type" => "com.friendica.post",
						"value" => $attached_data
						);

		if (isset($post["url"]) AND !isset($post["title"])) {
			$display_url = str_replace(array("http://www.", "https://www."), array("", ""), $post["url"]);
			$display_url = str_replace(array("http://", "https://"), array("", ""), $display_url);

			if (strlen($display_url) > 26)
				$display_url = substr($display_url, 0, 25)."…";

			$post["title"] = $display_url;
		}

		$text = appnet_create_entities($a, $b, $post);

		$data["entities"]["parse_markdown_links"] = true;

		if ($iscomment)
			$data["reply_to"] = substr($orig_post["uri"], 5);

		try {
			logger("appnet_send: sending message ".$b["id"]." ".$text." ".print_r($data, true), LOGGER_DEBUG);
			$ret = $app->createPost($text, $data);
			logger("appnet_send: send message ".$b["id"]." result: ".print_r($ret, true), LOGGER_DEBUG);
			if ($iscomment) {
				logger('appnet_send: Update extid '.$ret["id"]." for post id ".$b['id']);
				q("UPDATE `item` SET `extid` = '%s' WHERE `id` = %d",
					dbesc("adn::".$ret["id"]),
					intval($b['id'])
				);
			}
		}
		catch (AppDotNetException $e) {
			logger("appnet_send: Error sending message ".$b["id"]." ".appnet_error($e->getMessage()));
		}
	}
}

function appnet_action($a, $uid, $pid, $action) {
	require_once 'addon/appnet/AppDotNet.php';

	$token        = get_pconfig($uid,'appnet','token');
	$clientId     = get_pconfig($uid,'appnet','clientid');
	$clientSecret = get_pconfig($uid,'appnet','clientsecret');

	$app = new AppDotNet($clientId, $clientSecret);
	$app->setAccessToken($token);

	logger("appnet_action '".$action."' ID: ".$pid, LOGGER_DATA);

	try {
		switch ($action) {
			case "delete":
				$result = $app->deletePost($pid);
				break;
			case "like":
				$result = $app->starPost($pid);
				break;
			case "unlike":
				$result = $app->unstarPost($pid);
				break;
		}
		logger("appnet_action '".$action."' send, result: " . print_r($result, true), LOGGER_DEBUG);
	}
	catch (AppDotNetException $e) {
		logger("appnet_action: Error sending action ".$action." pid ".$pid." ".appnet_error($e->getMessage()), LOGGER_DEBUG);
	}
}

function appnet_is_repost($a, $uid, $body) {
	$body = trim($body);

	// Skip if it isn't a pure repeated messages
	// Does it start with a share?
	if (strpos($body, "[share") > 0)
		return(false);

	// Does it end with a share?
	if (strlen($body) > (strrpos($body, "[/share]") + 8))
		return(false);

	$attributes = preg_replace("/\[share(.*?)\]\s?(.*?)\s?\[\/share\]\s?/ism","$1",$body);
	// Skip if there is no shared message in there
	if ($body == $attributes)
		return(false);

	$link = "";
	preg_match("/link='(.*?)'/ism", $attributes, $matches);
	if ($matches[1] != "")
		$link = $matches[1];

	preg_match('/link="(.*?)"/ism', $attributes, $matches);
	if ($matches[1] != "")
		$link = $matches[1];

	$id = preg_replace("=https?://alpha.app.net/(.*)/post/(.*)=ism", "$2", $link);
	if ($id == $link)
		return(false);

	logger('appnet_is_repost: Reposting id '.$id.' for user '.$uid, LOGGER_DEBUG);

	require_once 'addon/appnet/AppDotNet.php';

	$token        = get_pconfig($uid,'appnet','token');
	$clientId     = get_pconfig($uid,'appnet','clientid');
	$clientSecret = get_pconfig($uid,'appnet','clientsecret');

	$app = new AppDotNet($clientId, $clientSecret);
	$app->setAccessToken($token);

	try {
		$result = $app->repost($id);
		logger('appnet_is_repost: result '.print_r($result, true), LOGGER_DEBUG);
		return true;
	}
	catch (AppDotNetException $e) {
		logger('appnet_is_repost: error doing repost '.appnet_error($e->getMessage()), LOGGER_DEBUG);
		return false;
	}
}

function appnet_fetchstream($a, $uid) {
	require_once("addon/appnet/AppDotNet.php");
	require_once('include/items.php');

	$token = get_pconfig($uid,'appnet','token');
	$clientId     = get_pconfig($uid,'appnet','clientid');
	$clientSecret = get_pconfig($uid,'appnet','clientsecret');

	$app = new AppDotNet($clientId, $clientSecret);
	$app->setAccessToken($token);

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if(count($r))
		$me = $r[0];
	else {
		logger("appnet_fetchstream: Own contact not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$user = q("SELECT * FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1",
		intval($uid)
	);

	if(count($user))
		$user = $user[0];
	else {
		logger("appnet_fetchstream: Own user not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$ownid = get_pconfig($uid,'appnet','ownid');

	// Fetch stream
	$param = array("count" => 200, "include_deleted" => false, "include_directed_posts" => true,
			"include_html" => false, "include_post_annotations" => true);

	$lastid  = get_pconfig($uid, 'appnet', 'laststreamid');

	if ($lastid <> "")
		$param["since_id"] = $lastid;

	try {
		$stream = $app->getUserStream($param);
	}
	catch (AppDotNetException $e) {
		logger("appnet_fetchstream: Error fetching stream for user ".$uid." ".appnet_error($e->getMessage()));
	}

	$stream = array_reverse($stream);
	foreach ($stream AS $post) {
		$postarray = appnet_createpost($a, $uid, $post, $me, $user, $ownid, true);

		$item = item_store($postarray);
		logger('appnet_fetchstream: User '.$uid.' posted stream item '.$item);

		$lastid = $post["id"];

		if (($item != 0) AND ($postarray['contact-id'] != $me["id"])) {
			$r = q("SELECT `thread`.`iid` AS `parent` FROM `thread`
				INNER JOIN `item` ON `thread`.`iid` = `item`.`parent` AND `thread`.`uid` = `item`.`uid`
				WHERE `item`.`id` = %d AND `thread`.`mention` LIMIT 1", dbesc($item));

			if (count($r)) {
				require_once('include/enotify.php');
				notification(array(
					'type'         => NOTIFY_COMMENT,
					'notify_flags' => $user['notify-flags'],
					'language'     => $user['language'],
					'to_name'      => $user['username'],
					'to_email'     => $user['email'],
					'uid'          => $user['uid'],
					'item'         => $postarray,
					'link'         => $a->get_baseurl() . '/display/' . $user['nickname'] . '/' . $item,
					'source_name'  => $postarray['author-name'],
					'source_link'  => $postarray['author-link'],
					'source_photo' => $postarray['author-avatar'],
					'verb'         => ACTIVITY_POST,
					'otype'        => 'item',
					'parent'       => $r[0]["parent"],
				));
			}
		}
	}

	set_pconfig($uid, 'appnet', 'laststreamid', $lastid);

	// Fetch mentions
	$param = array("count" => 200, "include_deleted" => false, "include_directed_posts" => true,
			"include_html" => false, "include_post_annotations" => true);

	$lastid  = get_pconfig($uid, 'appnet', 'lastmentionid');

	if ($lastid <> "")
		$param["since_id"] = $lastid;

	try {
		$mentions = $app->getUserMentions("me", $param);
	}
	catch (AppDotNetException $e) {
		logger("appnet_fetchstream: Error fetching mentions for user ".$uid." ".appnet_error($e->getMessage()));
	}

	$mentions = array_reverse($mentions);
	foreach ($mentions AS $post) {
		$postarray = appnet_createpost($a, $uid, $post, $me, $user, $ownid, false);

		if (isset($postarray["id"]))
			$item = $postarray["id"];
		elseif (isset($postarray["body"])) {
			$item = item_store($postarray);
			logger('appnet_fetchstream: User '.$uid.' posted mention item '.$item);
		} else
			$item = 0;

		$lastid = $post["id"];

		if (($item != 0) AND ($postarray['contact-id'] != $me["id"])) {
			require_once('include/enotify.php');
			notification(array(
				'type'         => NOTIFY_TAGSELF,
				'notify_flags' => $user['notify-flags'],
				'language'     => $user['language'],
				'to_name'      => $user['username'],
				'to_email'     => $user['email'],
				'uid'          => $user['uid'],
				'item'         => $postarray,
				'link'         => $a->get_baseurl() . '/display/' . $user['nickname'] . '/' . $item,
				'source_name'  => $postarray['author-name'],
				'source_link'  => $postarray['author-link'],
				'source_photo' => $postarray['author-avatar'],
				'verb'         => ACTIVITY_TAG,
				'otype'        => 'item'
			));
		}
	}

	set_pconfig($uid, 'appnet', 'lastmentionid', $lastid);


/* To-Do
	$param = array("interaction_actions" => "star");
	$interactions = $app->getMyInteractions($param);
	foreach ($interactions AS $interaction)
		appnet_dolike($a, $uid, $interaction);
*/
}

function appnet_createpost($a, $uid, $post, $me, $user, $ownid, $createuser, $threadcompletion = true) {
	require_once('include/items.php');

	if ($post["machine_only"])
		return;

	if ($post["is_deleted"])
		return;

	$postarray = array();
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['verb'] = ACTIVITY_POST;
	$postarray['network'] =  dbesc(NETWORK_APPNET);
	$postarray['uri'] = "adn::".$post["id"];

	$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
		dbesc($postarray['uri']),
		intval($uid)
		);

	if (count($r))
		return($r[0]);

	$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
		dbesc($postarray['uri']),
		intval($uid)
		);

	if (count($r))
		return($r[0]);

	$postarray['parent-uri'] = "adn::".$post["thread_id"];
	if (isset($post["reply_to"]) AND ($post["reply_to"] != "")) {
		$postarray['thr-parent'] = "adn::".$post["reply_to"];

		// Complete the thread if the parent doesn't exists
		if ($threadcompletion) {
			$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($postarray['thr-parent']),
				intval($uid)
				);
			if (!count($r)) {
				require_once("addon/appnet/AppDotNet.php");

				$token = get_pconfig($uid,'appnet','token');
				$clientId     = get_pconfig($uid,'appnet','clientid');
				$clientSecret = get_pconfig($uid,'appnet','clientsecret');

				$app = new AppDotNet($clientId, $clientSecret);
				$app->setAccessToken($token);

				$param = array("count" => 200, "include_deleted" => false, "include_directed_posts" => true,
						"include_html" => false, "include_post_annotations" => true);
				try {
					$thread = $app->getPostReplies($post["thread_id"], $param);
				}
				catch (AppDotNetException $e) {
					logger("appnet_createpost: Error fetching thread for user ".$uid." ".appnet_error($e->getMessage()));
				}
				$thread = array_reverse($thread);
				foreach ($thread AS $tpost) {
					$threadpost = appnet_createpost($a, $uid, $tpost, $me, $user, $ownid, $createuser, false);
					$item = item_store($threadpost);
				}
			}
		}
	} else
		$postarray['thr-parent'] = $postarray['uri'];

	$postarray['plink'] = $post["canonical_url"];

	if (($post["user"]["id"] != $ownid) OR ($postarray['thr-parent'] == $postarray['uri'])) {
		$postarray['owner-name'] = $post["user"]["name"];
		$postarray['owner-link'] = $post["user"]["canonical_url"];
		$postarray['owner-avatar'] = $post["user"]["avatar_image"]["url"];
		$postarray['contact-id'] = appnet_fetchcontact($a, $uid, $post["user"], $me, $createuser);
	} else {
		$postarray['owner-name'] = $me["name"];
		$postarray['owner-link'] = $me["url"];
		$postarray['owner-avatar'] = $me["thumb"];
		$postarray['contact-id'] = $me["id"];
	}

	$links = array();

	if (is_array($post["repost_of"])) {
		$postarray['author-name'] = $post["repost_of"]["user"]["name"];
		$postarray['author-link'] = $post["repost_of"]["user"]["canonical_url"];
		$postarray['author-avatar'] = $post["repost_of"]["user"]["avatar_image"]["url"];

		$content = $post["repost_of"];
	} else {
		$postarray['author-name'] = $postarray['owner-name'];
		$postarray['author-link'] = $postarray['owner-link'];
		$postarray['author-avatar'] = $postarray['owner-avatar'];

		$content = $post;
	}

	if (is_array($content["entities"])) {
		$converted = appnet_expand_entities($a, $content["text"], $content["entities"]);
		$postarray['body'] = $converted["body"];
		$postarray['tag'] = $converted["tags"];
	} else
		$postarray['body'] = $content["text"];

	if (sizeof($content["entities"]["links"]))
		foreach($content["entities"]["links"] AS $link) {
			$url = normalise_link($link["url"]);
			$links[$url] = $link["url"];
		}

	if (sizeof($content["annotations"]))
		foreach($content["annotations"] AS $annotation) {
			if ($annotation[type] == "net.app.core.oembed") {
				if (isset($annotation["value"]["embeddable_url"])) {
					$url = normalise_link($annotation["value"]["embeddable_url"]);
					if (isset($links[$url]))
						unset($links[$url]);
				}
			} elseif ($annotation[type] == "com.friendica.post") {
				// Nur zum Testen deaktiviert
				//$links = array();
				//if (isset($annotation["value"]["post-title"]))
				//	$postarray['title'] = $annotation["value"]["post-title"];

				//if (isset($annotation["value"]["post-body"]))
				//	$postarray['body'] = $annotation["value"]["post-body"];

				//if (isset($annotation["value"]["post-tag"]))
				//	$postarray['tag'] = $annotation["value"]["post-tag"];

				if (isset($annotation["value"]["author-name"]))
					$postarray['author-name'] = $annotation["value"]["author-name"];

				if (isset($annotation["value"]["author-link"]))
					$postarray['author-link'] = $annotation["value"]["author-link"];

				if (isset($annotation["value"]["author-avatar"]))
					$postarray['author-avatar'] = $annotation["value"]["author-avatar"];
			}

		}

	$page_info = "";

	if (is_array($content["annotations"])) {
		$photo = appnet_expand_annotations($a, $content["annotations"]);
		if (($photo["large"] != "") AND ($photo["url"] != ""))
			$page_info = "\n[url=".$photo["url"]."][img]".$photo["large"]."[/img][/url]";
		elseif ($photo["url"] != "")
			$page_info = "\n[img]".$photo["url"]."[/img]";
	} else
		$photo = array("url" => "", "large" => "");

	if (sizeof($links)) {
		$link = array_pop($links);
		$url = "[url=".$link."]".$link."[/url]";

		$removedlink = trim(str_replace($url, "", $postarray['body']));

		if (($removedlink == "") OR strstr($postarray['body'], $removedlink))
			$postarray['body'] = $removedlink;

		$page_info = add_page_info($link, false, $photo["url"]);
	}

	$postarray['body'] .= $page_info;

	$postarray['created'] = datetime_convert('UTC','UTC',$post["created_at"]);
	$postarray['edited'] = datetime_convert('UTC','UTC',$post["created_at"]);

	$postarray['app'] = $post["source"]["name"];

	return($postarray);
}

function appnet_expand_entities($a, $body, $entities) {

	if (!function_exists('substr_unicode')) {
		function substr_unicode($str, $s, $l = null) {
			return join("", array_slice(
				preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
		}
	}

	$tags_arr = array();
	$replace = array();

	foreach ($entities["mentions"] AS $mention) {
		$url = "@[url=https://alpha.app.net/".rawurlencode($mention["name"])."]".$mention["name"]."[/url]";
		$tags_arr["@".$mention["name"]] = $url;
		$replace[$mention["pos"]] = array("pos"=> $mention["pos"], "len"=> $mention["len"], "replace"=> $url);
	}

	foreach ($entities["hashtags"] AS $hashtag) {
		$url = "#[url=".$a->get_baseurl()."/search?tag=".rawurlencode($hashtag["name"])."]".$hashtag["name"]."[/url]";
		$tags_arr["#".$hashtag["name"]] = $url;
		$replace[$hashtag["pos"]] = array("pos"=> $hashtag["pos"], "len"=> $hashtag["len"], "replace"=> $url);
	}

	foreach ($entities["links"] AS $links) {
		$url = "[url=".$links["url"]."]".$links["text"]."[/url]";
		if (isset($links["amended_len"]) AND ($links["amended_len"] > $links["len"]))
			$replace[$links["pos"]] = array("pos"=> $links["pos"], "len"=> $links["amended_len"], "replace"=> $url);
		else
			$replace[$links["pos"]] = array("pos"=> $links["pos"], "len"=> $links["len"], "replace"=> $url);
	}


	if (sizeof($replace)) {
		krsort($replace);
		foreach ($replace AS $entity) {
			$pre = substr_unicode($body, 0, $entity["pos"]);
			$post = substr_unicode($body, $entity["pos"] + $entity["len"]);
			//$pre = iconv_substr($body, 0, $entity["pos"], "UTF-8");
			//$post = iconv_substr($body, $entity["pos"] + $entity["len"], "UTF-8");

			$body = $pre.$entity["replace"].$post;
		}
	}

	return(array("body" => $body, "tags" => implode($tags_arr, ",")));
}

function appnet_expand_annotations($a, $annotations) {
	$photo = array("url" => "", "large" => "");
	foreach ($annotations AS $annotation) {
		if (($annotation[type] == "net.app.core.oembed") AND
			($annotation["value"]["type"] == "photo")) {
			if ($annotation["value"]["url"] != "")
				$photo["url"] = $annotation["value"]["url"];

			if ($annotation["value"]["thumbnail_large_url"] != "")
				$photo["large"] = $annotation["value"]["thumbnail_large_url"];

			//if (($annotation["value"]["thumbnail_large_url"] != "") AND ($annotation["value"]["url"] != ""))
			//	$embedded = "\n[url=".$annotation["value"]["url"]."][img]".$annotation["value"]["thumbnail_large_url"]."[/img][/url]";
			//elseif ($annotation["value"]["url"] != "")
			//	$embedded = "\n[img]".$annotation["value"]["url"]."[/img]";
		}
	}
	return $photo;
}

function appnet_fetchcontact($a, $uid, $contact, $me, $create_user) {
	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
		intval($uid), dbesc("adn::".$contact["id"]));

	if(!count($r) AND !$create_user)
		return($me);


	if (count($r) AND ($r[0]["readonly"] OR $r[0]["blocked"])) {
		logger("appnet_fetchcontact: Contact '".$r[0]["nick"]."' is blocked or readonly.", LOGGER_DEBUG);
		return(-1);
	}

	if(!count($r)) {
		// create contact record
		q("INSERT INTO `contact` (`uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
					`name`, `nick`, `photo`, `network`, `rel`, `priority`,
					`writable`, `blocked`, `readonly`, `pending` )
					VALUES ( %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, 0, 0, 0 ) ",
			intval($uid),
			dbesc(datetime_convert()),
			dbesc($contact["canonical_url"]),
			dbesc(normalise_link($contact["canonical_url"])),
			dbesc($contact["username"]."@app.net"),
			dbesc("adn::".$contact["id"]),
			dbesc(''),
			dbesc("adn::".$contact["id"]),
			dbesc($contact["name"]),
			dbesc($contact["username"]),
			dbesc($contact["avatar_image"]["url"]),
			dbesc(NETWORK_APPNET),
			intval(CONTACT_IS_FRIEND),
			intval(1),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d LIMIT 1",
			dbesc("adn::".$contact["id"]),
			intval($uid)
			);

		if(! count($r))
			return(false);

		$contact_id  = $r[0]['id'];

		$g = q("SELECT def_gid FROM user WHERE uid = %d LIMIT 1",
			intval($uid)
		);

		if($g && intval($g[0]['def_gid'])) {
			require_once('include/group.php');
			group_add_member($uid,'',$contact_id,$g[0]['def_gid']);
		}

		require_once("Photo.php");

		$photos = import_profile_photo($contact["avatar_image"]["url"],$uid,$contact_id);

		q("UPDATE `contact` SET `photo` = '%s',
					`thumb` = '%s',
					`micro` = '%s',
					`name-date` = '%s',
					`uri-date` = '%s',
					`avatar-date` = '%s'
				WHERE `id` = %d",
			dbesc($photos[0]),
			dbesc($photos[1]),
			dbesc($photos[2]),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			intval($contact_id)
		);

	} else {
		// update profile photos once every two weeks as we have no notification of when they change.

		//$update_photo = (($r[0]['avatar-date'] < datetime_convert('','','now -2 days')) ? true : false);
		$update_photo = ($r[0]['avatar-date'] < datetime_convert('','','now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion

		if((! $r[0]['photo']) || (! $r[0]['thumb']) || (! $r[0]['micro']) || ($update_photo)) {

			logger("appnet_fetchcontact: Updating contact ".$contact["username"], LOGGER_DEBUG);

			require_once("Photo.php");

			$photos = import_profile_photo($contact["avatar_image"]["url"], $uid, $r[0]['id']);

			q("UPDATE `contact` SET `photo` = '%s',
						`thumb` = '%s',
						`micro` = '%s',
						`name-date` = '%s',
						`uri-date` = '%s',
						`avatar-date` = '%s',
						`url` = '%s',
						`nurl` = '%s',
						`addr` = '%s',
						`name` = '%s',
						`nick` = '%s'
					WHERE `id` = %d",
				dbesc($photos[0]),
				dbesc($photos[1]),
				dbesc($photos[2]),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc($contact["canonical_url"]),
				dbesc(normalise_link($contact["canonical_url"])),
				dbesc($contact["username"]."@app.net"),
				dbesc($contact["name"]),
				dbesc($contact["username"]),
				intval($r[0]['id'])
			);
		}
	}

	return($r[0]["id"]);
}

function appnet_cron($a,$b) {
	$last = get_config('appnet','last_poll');

	$poll_interval = intval(get_config('appnet','poll_interval'));
	if(! $poll_interval)
		$poll_interval = APPNET_DEFAULT_POLL_INTERVAL;

	if($last) {
		$next = $last + ($poll_interval * 60);
		if($next > time()) {
			logger('appnet_cron: poll intervall not reached');
			return;
		}
	}
	logger('appnet_cron: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'appnet' AND `k` = 'import' AND `v` = '1' ORDER BY RAND()");
	if(count($r)) {
		foreach($r as $rr) {
			logger('appnet_cron: importing timeline from user '.$rr['uid']);
			appnet_fetchstream($a, $rr["uid"]);
		}
	}

	logger('appnet_cron: cron_end');

	set_config('appnet','last_poll', time());
}

function appnet_error($msg) {
        $msg = trim($msg);
        $pos = strrpos($msg, "\r\n\r\n");

        if (!$pos)
                return($msg);

        $msg = substr($msg, $pos + 4);

        $error = json_decode($msg);

        if ($error == NULL)
                return($msg);

	if (isset($error->meta->error_message))
		return($error->meta->error_message);
        else
                return(print_r($error));
}
