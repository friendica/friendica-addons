<?php
/**
 * Name: pump.io Post Connector
 * Description: Post to pump.io
 * Version: 0.2
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */
require('addon/pumpio/oauth/http.php');
require('addon/pumpio/oauth/oauth_client.php');

define('PUMPIO_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function pumpio_install() {
    register_hook('post_local',           'addon/pumpio/pumpio.php', 'pumpio_post_local');
    register_hook('notifier_normal',      'addon/pumpio/pumpio.php', 'pumpio_send');
    register_hook('jot_networks',         'addon/pumpio/pumpio.php', 'pumpio_jot_nets');
    register_hook('connector_settings',      'addon/pumpio/pumpio.php', 'pumpio_settings');
    register_hook('connector_settings_post', 'addon/pumpio/pumpio.php', 'pumpio_settings_post');
    register_hook('cron', 'addon/pumpio/pumpio.php', 'pumpio_cron');
    register_hook('queue_predeliver', 'addon/pumpio/pumpio.php', 'pumpio_queue_hook');
}

function pumpio_uninstall() {
    unregister_hook('post_local',       'addon/pumpio/pumpio.php', 'pumpio_post_local');
    unregister_hook('notifier_normal',  'addon/pumpio/pumpio.php', 'pumpio_send');
    unregister_hook('jot_networks',     'addon/pumpio/pumpio.php', 'pumpio_jot_nets');
    unregister_hook('connector_settings',      'addon/pumpio/pumpio.php', 'pumpio_settings');
    unregister_hook('connector_settings_post', 'addon/pumpio/pumpio.php', 'pumpio_settings_post');
    unregister_hook('cron', 'addon/pumpio/pumpio.php', 'pumpio_cron');
    unregister_hook('queue_predeliver', 'addon/pumpio/pumpio.php', 'pumpio_queue_hook');
}

function pumpio_module() {}

function pumpio_content(&$a) {

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return '';
	}

	if (function_exists("apc_delete")) {
		$toDelete = new APCIterator('user', APC_ITER_VALUE);
		apc_delete($toDelete);
	}

	if (isset($a->argv[1]))
		switch ($a->argv[1]) {
			case "connect":
				$o = pumpio_connect($a);
				break;
			default:
				$o = print_r($a->argv, true);
				break;
		}
	else
		$o = pumpio_connect($a);

	return $o;
}

function pumpio_registerclient(&$a, $host) {

	$url = "https://".$host."/api/client/register";

        $params = array();

	$application_name  = get_config('pumpio', 'application_name');

	if ($application_name == "")
		$application_name = $a->get_hostname();

        $params["type"] = "client_associate";
        $params["contacts"] = $a->config['admin_email'];
        $params["application_type"] = "native";
        $params["application_name"] = $application_name;
        $params["logo_url"] = $a->get_baseurl()."/images/friendica-256.png";
        $params["redirect_uris"] = $a->get_baseurl()."/pumpio/connect";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_USERAGENT, "Friendica");

        $s = curl_exec($ch);
        $curl_info = curl_getinfo($ch);

        if ($curl_info["http_code"] == "200") {
                $values = json_decode($s);
		return($values);
        }
	return(false);
}

function pumpio_connect(&$a) {
	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Define the needed keys
	$consumer_key = get_pconfig(local_user(), 'pumpio','consumer_key');
	$consumer_secret = get_pconfig(local_user(), 'pumpio','consumer_secret');
	$hostname = get_pconfig(local_user(), 'pumpio','host');

	if ((($consumer_key == "") OR ($consumer_secret == "")) AND ($hostname != "")) {
		logger("pumpio_connect: register client");
		$clientdata = pumpio_registerclient($a, $hostname);
		set_pconfig(local_user(), 'pumpio','consumer_key', $clientdata->client_id);
		set_pconfig(local_user(), 'pumpio','consumer_secret', $clientdata->client_secret);

		$consumer_key = get_pconfig(local_user(), 'pumpio','consumer_key');
		$consumer_secret = get_pconfig(local_user(), 'pumpio','consumer_secret');

		logger("pumpio_connect: ckey: ".$consumer_key." csecrect: ".$consumer_secret);
	}

	if (($consumer_key == "") OR ($consumer_secret == "")) {
		logger("pumpio_connect: ".sprintf("Unable to register the client at the pump.io server '%s'.", $hostname));

		$o .= sprintf(t("Unable to register the client at the pump.io server '%s'."), $hostname);
		return($o);
	}

	// The callback URL is the script that gets called after the user authenticates with pumpio
	$callback_url = $a->get_baseurl()."/pumpio/connect";

	// Let's begin.  First we need a Request Token.  The request token is required to send the user
	// to pumpio's login page.

	// Create a new instance of the TumblrOAuth library.  For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$client = new oauth_client_class;
	$client->debug = 1;
	$client->server = '';
	$client->oauth_version = '1.0a';
	$client->request_token_url = 'https://'.$hostname.'/oauth/request_token';
	$client->dialog_url = 'https://'.$hostname.'/oauth/authorize';
	$client->access_token_url = 'https://'.$hostname.'/oauth/access_token';
	$client->url_parameters = false;
	$client->authorization_header = true;
	$client->redirect_uri = $callback_url;
	$client->client_id = $consumer_key;
	$client->client_secret = $consumer_secret;

	if (($success = $client->Initialize())) {
		if (($success = $client->Process())) {
			if (strlen($client->access_token)) {
				logger("pumpio_connect: otoken: ".$client->access_token." osecrect: ".$client->access_token_secret);
				set_pconfig(local_user(), "pumpio", "oauth_token", $client->access_token);
				set_pconfig(local_user(), "pumpio", "oauth_token_secret", $client->access_token_secret);
			}
		}
		$success = $client->Finalize($success);
	}
        if($client->exit)
	    $o = 'Could not connect to pumpio. Refresh the page or try again later.';

        if($success) {
		$o .= t("You are now authenticated to pumpio.");
		$o .= '<br /><a href="'.$a->get_baseurl().'/settings/connectors">'.t("return to the connector page").'</a>';
	} else
	    $o = 'Could not connect to pumpio. Refresh the page or try again later.';

	return($o);
}

function pumpio_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $pumpio_post = get_pconfig(local_user(),'pumpio','post');
    if(intval($pumpio_post) == 1) {
        $pumpio_defpost = get_pconfig(local_user(),'pumpio','post_by_default');
        $selected = ((intval($pumpio_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="pumpio_enable"' . $selected . ' value="1" /> '
            . t('Post to pumpio') . '</div>';
    }
}


function pumpio_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/pumpio/pumpio.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $import_enabled = get_pconfig(local_user(),'pumpio','import');
    $import_checked = (($import_enabled) ? ' checked="checked" ' : '');

    $enabled = get_pconfig(local_user(),'pumpio','post');
    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'pumpio','post_by_default');
    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

    $public_enabled = get_pconfig(local_user(),'pumpio','public');
    $public_checked = (($public_enabled) ? ' checked="checked" ' : '');

    $mirror_enabled = get_pconfig(local_user(),'pumpio','mirror');
    $mirror_checked = (($mirror_enabled) ? ' checked="checked" ' : '');

    $servername = get_pconfig(local_user(), "pumpio", "host");
    $username = get_pconfig(local_user(), "pumpio", "user");

    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Pump.io Post Settings') . '</h3>';

    $s .= '<div id="pumpio-username-wrapper">';
    $s .= '<label id="pumpio-username-label" for="pumpio-username">'.t('pump.io username (without the servername)').'</label>';
    $s .= '<input id="pumpio-username" type="text" name="pumpio_user" value="'.$username.'" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="pumpio-servername-wrapper">';
    $s .= '<label id="pumpio-servername-label" for="pumpio-servername">'.t('pump.io servername (without "http://" or "https://" )').'</label>';
    $s .= '<input id="pumpio-servername" type="text" name="pumpio_host" value="'.$servername.'" />';
    $s .= '</div><div class="clear"></div>';

    if (($username != '') AND ($servername != '')) {

	$oauth_token = get_pconfig(local_user(), "pumpio", "oauth_token");
	$oauth_token_secret = get_pconfig(local_user(), "pumpio", "oauth_token_secret");

	$s .= '<div id="pumpio-password-wrapper">';
	if (($oauth_token == "") OR ($oauth_token_secret == "")) {
		$s .= '<div id="pumpio-authenticate-wrapper">';
		$s .= '<a href="'.$a->get_baseurl().'/pumpio/connect">'.t("Authenticate your pump.io connection").'</a>';
		$s .= '</div><div class="clear"></div>';
	} else {
		$s .= '<div id="pumpio-import-wrapper">';
		$s .= '<label id="pumpio-import-label" for="pumpio-import">' . t('Import the remote timeline') . '</label>';
		$s .= '<input id="pumpio-import" type="checkbox" name="pumpio_import" value="1" ' . $import_checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="pumpio-enable-wrapper">';
		$s .= '<label id="pumpio-enable-label" for="pumpio-checkbox">' . t('Enable pump.io Post Plugin') . '</label>';
		$s .= '<input id="pumpio-checkbox" type="checkbox" name="pumpio" value="1" ' . $checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="pumpio-bydefault-wrapper">';
		$s .= '<label id="pumpio-bydefault-label" for="pumpio-bydefault">' . t('Post to pump.io by default') . '</label>';
		$s .= '<input id="pumpio-bydefault" type="checkbox" name="pumpio_bydefault" value="1" ' . $def_checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="pumpio-public-wrapper">';
		$s .= '<label id="pumpio-public-label" for="pumpio-public">' . t('Should posts be public?') . '</label>';
		$s .= '<input id="pumpio-public" type="checkbox" name="pumpio_public" value="1" ' . $public_checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="pumpio-mirror-wrapper">';
		$s .= '<label id="pumpio-mirror-label" for="pumpio-mirror">' . t('Mirror all public posts') . '</label>';
		$s .= '<input id="pumpio-mirror" type="checkbox" name="pumpio_mirror" value="1" ' . $mirror_checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="pumpio-delete-wrapper">';
		$s .= '<label id="pumpio-delete-label" for="pumpio-delete">' . t('Check to delete this preset') . '</label>';
		$s .= '<input id="pumpio-delete" type="checkbox" name="pumpio_delete" value="1" />';
		$s .= '</div><div class="clear"></div>';
	}

	$s .= '</div><div class="clear"></div>';
    }

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="pumpio-submit" name="pumpio-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function pumpio_settings_post(&$a,&$b) {

	if(x($_POST,'pumpio-submit')) {
		if(x($_POST,'pumpio_delete')) {
			set_pconfig(local_user(),'pumpio','consumer_key','');
			set_pconfig(local_user(),'pumpio','consumer_secret','');
			set_pconfig(local_user(),'pumpio','host','');
			set_pconfig(local_user(),'pumpio','oauth_token','');
			set_pconfig(local_user(),'pumpio','oauth_token_secret','');
			set_pconfig(local_user(),'pumpio','post',false);
			set_pconfig(local_user(),'pumpio','post_by_default',false);
			set_pconfig(local_user(),'pumpio','user','');
		} else {
			// filtering the username if it is filled wrong
			$user = $_POST['pumpio_user'];
			if (strstr($user, "@")) {
				$pos = strpos($user, "@");
				if ($pos > 0)
					$user = substr($user, 0, $pos);
			}

			// Filtering the hostname if someone is entering it with "http"
			$host = $_POST['pumpio_host'];
			$host = trim($host);
			$host = str_replace(array("https://", "http://"), array("", ""), $host);

			set_pconfig(local_user(),'pumpio','post',intval($_POST['pumpio']));
			set_pconfig(local_user(),'pumpio','import',$_POST['pumpio_import']);
			set_pconfig(local_user(),'pumpio','host',$host);
			set_pconfig(local_user(),'pumpio','user',$user);
			set_pconfig(local_user(),'pumpio','public',$_POST['pumpio_public']);
			set_pconfig(local_user(),'pumpio','mirror',$_POST['pumpio_mirror']);
			set_pconfig(local_user(),'pumpio','post_by_default',intval($_POST['pumpio_bydefault']));
		}
	}
}

function pumpio_post_local(&$a,&$b) {

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	$pumpio_post   = intval(get_pconfig(local_user(),'pumpio','post'));

	$pumpio_enable = (($pumpio_post && x($_REQUEST,'pumpio_enable')) ? intval($_REQUEST['pumpio_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'pumpio','post_by_default')))
		$pumpio_enable = 1;

	if(! $pumpio_enable)
		return;

	if(strlen($b['postopts']))
		$b['postopts'] .= ',';

	$b['postopts'] .= 'pumpio';
}




function pumpio_send(&$a,&$b) {

	if (!get_pconfig($b["uid"],'pumpio','import')) {
		if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
			return;
	}

	logger("pumpio_send: parameter ".print_r($b, true));

	if($b['parent'] != $b['id']) {
		// Looking if its a reply to a pumpio post
		$r = q("SELECT item.* FROM item, contact WHERE item.id = %d AND item.uid = %d AND contact.id = `contact-id` AND contact.network='%s'LIMIT 1",
			intval($b["parent"]),
			intval($b["uid"]),
			dbesc(NETWORK_PUMPIO));

		if(!count($r)) {
			logger("pumpio_send: no pumpio post ".$b["parent"]);
			return;
		} else {
			$iscomment = true;
			$orig_post = $r[0];
		}
	} else {
		$iscomment = false;

		$receiver = pumpio_getreceiver($a, $b);

		logger("pumpio_send: receiver ".print_r($receiver, true));

		if (!count($receiver) AND ($b['private'] OR !strstr($b['postopts'],'pumpio')))
			return;
	}

	if($b['verb'] == ACTIVITY_LIKE) {
		if ($b['deleted'])
			pumpio_action($a, $b["uid"], $b["thr-parent"], "unlike");
		else
			pumpio_action($a, $b["uid"], $b["thr-parent"], "like");
		return;
	}

	if($b['verb'] == ACTIVITY_DISLIKE)
		return;

	if (($b['verb'] == ACTIVITY_POST) AND ($b['created'] !== $b['edited']) AND !$b['deleted'])
			pumpio_action($a, $b["uid"], $b["uri"], "update", $b["body"]);

	if (($b['verb'] == ACTIVITY_POST) AND $b['deleted'])
			pumpio_action($a, $b["uid"], $b["uri"], "delete");

	if($b['deleted'] || ($b['created'] !== $b['edited']))
		return;

	// if post comes from pump.io don't send it back
	if($b['app'] == "pump.io")
		return;


	$oauth_token = get_pconfig($b['uid'], "pumpio", "oauth_token");
	$oauth_token_secret = get_pconfig($b['uid'], "pumpio", "oauth_token_secret");
	$consumer_key = get_pconfig($b['uid'], "pumpio","consumer_key");
	$consumer_secret = get_pconfig($b['uid'], "pumpio","consumer_secret");

	$host = get_pconfig($b['uid'], "pumpio", "host");
	$user = get_pconfig($b['uid'], "pumpio", "user");
	$public = get_pconfig($b['uid'], "pumpio", "public");

	if($oauth_token && $oauth_token_secret) {

		require_once('include/bbcode.php');

		$title = trim($b['title']);

		if ($title != '')
			$title = "<h4>".$title."</h4>";

		$content = bbcode($b['body'], false, false);

		// Enhance the way, videos are displayed
		$content = preg_replace('/<a.*?href="(https?:\/\/www.youtube.com\/.*?)".*?>(.*?)<\/a>/ism',"\n[url]$1[/url]\n",$content);
		$content = preg_replace('/<a.*?href="(https?:\/\/youtu.be\/.*?)".*?>(.*?)<\/a>/ism',"\n$1\n",$content);
		$content = preg_replace('/<a.*?href="(https?:\/\/vimeo.com\/.*?)".*?>(.*?)<\/a>/ism',"\n$1\n",$content);
		$content = preg_replace('/<a.*?href="(https?:\/\/player.vimeo.com\/.*?)".*?>(.*?)<\/a>/ism',"\n$1\n",$content);

		$URLSearchString = "^\[\]";
		$content = preg_replace_callback("/\[url\]([$URLSearchString]*)\[\/url\]/ism",'tryoembed',$content);

		$params = array();

		$params["verb"] = "post";

		if (!$iscomment) {
			$params["object"] = array(
						'objectType' => "note",
						'content' => $title.$content);

			if (count($receiver["to"]))
				$params["to"] = $receiver["to"];

			if (count($receiver["bto"]))
				$params["bto"] = $receiver["bto"];

			if (count($receiver["cc"]))
				$params["cc"] = $receiver["cc"];

			if (count($receiver["bcc"]))
				$params["bcc"] = $receiver["bcc"];

		 } else {
			$inReplyTo = array("id" => $orig_post["uri"],
					"objectType" => "note");

			$params["object"] = array(
						'objectType' => "comment",
						'content' => $title.$content,
						'inReplyTo' => $inReplyTo);
		}

		$client = new oauth_client_class;
		$client->oauth_version = '1.0a';
		$client->url_parameters = false;
		$client->authorization_header = true;
		$client->access_token = $oauth_token;
		$client->access_token_secret = $oauth_token_secret;
		$client->client_id = $consumer_key;
		$client->client_secret = $consumer_secret;

		$username = $user.'@'.$host;
		$url = 'https://'.$host.'/api/user/'.$user.'/feed';

		$success = $client->CallAPI($url, 'POST', $params, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);

		if($success) {
			$post_id = $user->object->id;
			logger('pumpio_send '.$username.': success '.$post_id);
			if($post_id AND $iscomment) {
				logger('pumpio_send '.$username.': Update extid '.$post_id." for post id ".$b['id']);
				q("UPDATE `item` SET `extid` = '%s' WHERE `id` = %d LIMIT 1",
					dbesc($post_id),
					intval($b['id'])
				);
			}
		} else {
			logger('pumpio_send '.$username.': '.$url.' general error: ' . print_r($user,true));

			$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", $b['uid']);
			if (count($r))
				$a->contact = $r[0]["id"];

			$s = serialize(array('url' => $url, 'item' => $b['id'], 'post' => $params));
			require_once('include/queue_fn.php');
			add_to_queue($a->contact,NETWORK_PUMPIO,$s);
			notice(t('Pump.io post failed. Queued for retry.').EOL);
		}

	}
}

function pumpio_action(&$a, $uid, $uri, $action, $content) {

	// Don't do likes and other stuff if you don't import the timeline
	if (!get_pconfig($uid,'pumpio','import'))
		return;

	$ckey    = get_pconfig($uid, 'pumpio', 'consumer_key');
	$csecret = get_pconfig($uid, 'pumpio', 'consumer_secret');
	$otoken  = get_pconfig($uid, 'pumpio', 'oauth_token');
	$osecret = get_pconfig($uid, 'pumpio', 'oauth_token_secret');
	$hostname = get_pconfig($uid, 'pumpio','host');
	$username = get_pconfig($uid, "pumpio", "user");

	$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($uri),
				intval($uid)
	);

	if (!count($r))
		return;

	$orig_post = $r[0];

	if ($orig_post["extid"] AND !strstr($orig_post["extid"], "/proxy/"))
		$uri = $orig_post["extid"];
	else
		$uri = $orig_post["uri"];

	if (strstr($uri, "/api/comment/"))
		$objectType = "comment";
	elseif (strstr($uri, "/api/note/"))
		$objectType = "note";
	elseif (strstr($uri, "/api/image/"))
		$objectType = "image";

	$params["verb"] = $action;
	$params["object"] = array('id' => $uri,
				"objectType" => $objectType,
				"content" => $content);

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$url = 'https://'.$hostname.'/api/user/'.$username.'/feed';

	$success = $client->CallAPI($url, 'POST', $params, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);

	if($success)
		logger('pumpio_action '.$username.' '.$action.': success '.$uri);
	else {
		logger('pumpio_action '.$username.' '.$action.': general error: '.$uri.' '.print_r($user,true));

		$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", $b['uid']);
		if (count($r))
			$a->contact = $r[0]["id"];

		$s = serialize(array('url' => $url, 'item' => $orig_post["id"], 'post' => $params));
		require_once('include/queue_fn.php');
		add_to_queue($a->contact,NETWORK_PUMPIO,$s);
		notice(t('Pump.io like failed. Queued for retry.').EOL);
	}
}


function pumpio_cron(&$a,$b) {
        $last = get_config('pumpio','last_poll');

        $poll_interval = intval(get_config('pumpio','poll_interval'));
        if(! $poll_interval)
                $poll_interval = PUMPIO_DEFAULT_POLL_INTERVAL;

        if($last) {
                $next = $last + ($poll_interval * 60);
                if($next > time()) {
                        logger('pumpio: poll intervall not reached');
                        return;
                }
        }
        logger('pumpio: cron_start');

        $r = q("SELECT * FROM `pconfig` WHERE `cat` = 'pumpio' AND `k` = 'mirror' AND `v` = '1' ORDER BY RAND() ");
        if(count($r)) {
                foreach($r as $rr) {
                        logger('pumpio: mirroring user '.$rr['uid']);
                        pumpio_fetchtimeline($a, $rr['uid']);
                }
        }

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'pumpio' AND `k` = 'import' AND `v` = '1' ORDER BY RAND() ");
	if(count($r)) {
		foreach($r as $rr) {
			logger('pumpio: importing timeline from user '.$rr['uid']);
			pumpio_fetchinbox($a, $rr['uid']);

			// check for new contacts once a day
			$last_contact_check = get_pconfig($rr['uid'],'pumpio','contact_check');
			if($last_contact_check)
				$next_contact_check = $last_contact_check + 86400;
			else
				$next_contact_check = 0;

			if($next_contact_check <= time()) {
				pumpio_getallusers($a, $rr["uid"]);
				set_pconfig($rr['uid'],'pumpio','contact_check',time());
			}
		}
	}

	logger('pumpio: cron_end');

	set_config('pumpio','last_poll', time());
}

function pumpio_fetchtimeline(&$a, $uid) {
	$ckey    = get_pconfig($uid, 'pumpio', 'consumer_key');
	$csecret = get_pconfig($uid, 'pumpio', 'consumer_secret');
	$otoken  = get_pconfig($uid, 'pumpio', 'oauth_token');
	$osecret = get_pconfig($uid, 'pumpio', 'oauth_token_secret');
	$lastdate = get_pconfig($uid, 'pumpio', 'lastdate');
	$hostname = get_pconfig($uid, 'pumpio','host');
	$username = get_pconfig($uid, "pumpio", "user");

	$application_name  = get_config('pumpio', 'application_name');

	if ($application_name == "")
		$application_name = $a->get_hostname();

	$first_time = ($lastdate == "");

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$url = 'https://'.$hostname.'/api/user/'.$username.'/feed/major';

	logger('pumpio: fetching for user '.$uid.' '.$url.' C:'.$client->client_id.' CS:'.$client->client_secret.' T:'.$client->access_token.' TS:'.$client->access_token_secret);

	$username = $user.'@'.$host;

	$success = $client->CallAPI($url, 'GET', array(), array('FailOnAccessError'=>true), $user);

	if (!$success) {
		logger('pumpio: error fetching posts for user '.$uid." ".$username." ".print_r($user, true));
		return;
	}

	$posts = array_reverse($user->items);

	$initiallastdate = $lastdate;
	$lastdate = '';

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->generator->published <= $initiallastdate)
				continue;

			if ($lastdate < $post->generator->published)
				$lastdate = $post->generator->published;

			if ($first_time)
				continue;

			$receiptians = array();
			if (@is_array($post->cc))
				$receiptians = array_merge($receiptians, $post->cc);

			if (@is_array($post->to))
				$receiptians = array_merge($receiptians, $post->to);

			$public = false;
			foreach ($receiptians AS $receiver)
				if (is_string($receiver->objectType))
					if ($receiver->id == "http://activityschema.org/collection/public")
						$public = true;

			if ($public AND !strstr($post->generator->displayName, $application_name)) {
				require_once('include/html2bbcode.php');

				$_SESSION["authenticated"] = true;
				$_SESSION["uid"] = $uid;

				unset($_REQUEST);
				$_REQUEST["type"] = "wall";
				$_REQUEST["api_source"] = true;
				$_REQUEST["profile_uid"] = $uid;
				$_REQUEST["source"] = "pump.io";

				if ($post->object->displayName != "")
					$_REQUEST["title"] = html2bbcode($post->object->displayName);
				else
					$_REQUEST["title"] = "";

				$_REQUEST["body"] = html2bbcode($post->object->content);

				if ($post->object->fullImage->url != "")
					$_REQUEST["body"] = "[url=".$post->object->fullImage->url."][img]".$post->object->image->url."[/img][/url]\n".$_REQUEST["body"];

				logger('pumpio: posting for user '.$uid);

				require_once('mod/item.php');

				item_post($a);
				logger('pumpio: posting done - user '.$uid);
			}
		}
	}

	if ($lastdate != 0)
		set_pconfig($uid,'pumpio','lastdate', $lastdate);
}

function pumpio_dounlike(&$a, $uid, $self, $post, $own_id) {
	// Searching for the unliked post
	// Two queries for speed issues
	$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($post->object->id),
				intval($uid)
		);

	if (count($r))
		$orig_post = $r[0];
	else {
		$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($post->object->id),
					intval($uid)
			);

		if (!count($r))
			return;
		else
			$orig_post = $r[0];
	}

	$contactid = 0;

	if(link_compare($post->actor->url, $own_id)) {
		$contactid = $self[0]['id'];
	} else {
		$r = q("SELECT * FROM `contact` WHERE `url` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
			dbesc($post->actor->url),
			intval($uid)
		);

		if(count($r))
			$contactid = $r[0]['id'];

		if($contactid == 0)
			$contactid = $orig_post['contact-id'];
	}

	$r = q("UPDATE `item` SET `deleted` = 1, `unseen` = 1, `changed` = '%s' WHERE `verb` = '%s' AND `uid` = %d AND `contact-id` = %d AND `thr-parent` = '%s'",
		dbesc(datetime_convert()),
		dbesc(ACTIVITY_LIKE),
		intval($uid),
		intval($contactid),
		dbesc($orig_post['uri'])
	);

	if(count($r))
		logger("pumpio_dounlike: unliked existing like. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
	else
		logger("pumpio_dounlike: not found. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
}

function pumpio_dolike(&$a, $uid, $self, $post, $own_id) {

	// Searching for the liked post
	// Two queries for speed issues
	$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($post->object->id),
				intval($uid)
		);

	if (count($r))
		$orig_post = $r[0];
	else {
		$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($post->object->id),
					intval($uid)
			);

		if (!count($r))
			return;
		else
			$orig_post = $r[0];
	}

	$contactid = 0;

	if(link_compare($post->actor->url, $own_id)) {
		$contactid = $self[0]['id'];
		$post->actor->displayName = $self[0]['name'];
		$post->actor->url = $self[0]['url'];
		$post->actor->image->url = $self[0]['photo'];
	} else {
		$r = q("SELECT * FROM `contact` WHERE `url` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
			dbesc($post->actor->url),
			intval($uid)
		);

		if(count($r))
			$contactid = $r[0]['id'];

		if($contactid == 0)
			$contactid = $orig_post['contact-id'];
	}

	$r = q("SELECT parent FROM `item` WHERE `verb` = '%s' AND `uid` = %d AND `contact-id` = %d AND `thr-parent` = '%s' LIMIT 1",
		dbesc(ACTIVITY_LIKE),
		intval($uid),
		intval($contactid),
		dbesc($orig_post['uri'])
	);

	if(count($r)) {
		logger("pumpio_dolike: found existing like. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
		return;
	}

	$likedata = array();
	$likedata['parent'] = $orig_post['id'];
	$likedata['verb'] = ACTIVITY_LIKE;
	$likedata['gravity'] = 3;
	$likedata['uid'] = $uid;
	$likedata['wall'] = 0;
	$likedata['uri'] = item_new_uri($a->get_baseurl(), $uid);
	$likedata['parent-uri'] = $orig_post["uri"];
	$likedata['contact-id'] = $contactid;
	$likedata['app'] = $post->generator->displayName;
	$likedata['verb'] = ACTIVITY_LIKE;
	$likedata['author-name'] = $post->actor->displayName;
	$likedata['author-link'] = $post->actor->url;
	$likedata['author-avatar'] = $post->actor->image->url;

	$author  = '[url=' . $likedata['author-link'] . ']' . $likedata['author-name'] . '[/url]';
	$objauthor =  '[url=' . $orig_post['author-link'] . ']' . $orig_post['author-name'] . '[/url]';
	$post_type = t('status');
	$plink = '[url=' . $orig_post['plink'] . ']' . $post_type . '[/url]';
	$likedata['object-type'] = ACTIVITY_OBJ_NOTE;

	$likedata['body'] = sprintf( t('%1$s likes %2$s\'s %3$s'), $author, $objauthor, $plink);

	$likedata['object'] = '<object><type>' . ACTIVITY_OBJ_NOTE . '</type><local>1</local>' .
		'<id>' . $orig_post['uri'] . '</id><link>' . xmlify('<link rel="alternate" type="text/html" href="' . xmlify($orig_post['plink']) . '" />') . '</link><title>' . $orig_post['title'] . '</title><content>' . $orig_post['body'] . '</content></object>';

	$ret = item_store($likedata);

	logger("pumpio_dolike: ".$ret." User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
}

function pumpio_get_contact($uid, $contact) {

	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `url` = '%s' LIMIT 1",
		intval($uid), dbesc($contact->url));

	if(!count($r)) {
		// create contact record
		q("INSERT INTO `contact` ( `uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
					`name`, `nick`, `photo`, `network`, `rel`, `priority`,
					`writable`, `blocked`, `readonly`, `pending` )
				VALUES ( %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, 0, 0, 0 ) ",
			intval($uid),
			dbesc(datetime_convert()),
			dbesc($contact->url),
			dbesc(normalise_link($contact->url)),
			dbesc(str_replace("acct:", "", $contact->id)),
			dbesc(''),
			dbesc($contact->id), // What is it for?
			dbesc('pump.io ' . $contact->id), // What is it for?
			dbesc($contact->displayName),
			dbesc($contact->preferredUsername),
			dbesc($contact->image->url),
			dbesc(NETWORK_PUMPIO),
			intval(CONTACT_IS_FRIEND),
			intval(1),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `url` = '%s' AND `uid` = %d LIMIT 1",
			dbesc($contact->url),
			intval($uid)
			);

		if(! count($r))
			return(false);

		$contact_id  = $r[0]['id'];

		$g = q("select def_gid from user where uid = %d limit 1",
			intval($uid)
		);

		if($g && intval($g[0]['def_gid'])) {
			require_once('include/group.php');
			group_add_member($uid,'',$contact_id,$g[0]['def_gid']);
		}

		require_once("Photo.php");

		$photos = import_profile_photo($contact->image->url,$uid,$contact_id);

		q("UPDATE `contact` SET `photo` = '%s',
					`thumb` = '%s',
					`micro` = '%s',
					`name-date` = '%s',
					`uri-date` = '%s',
					`avatar-date` = '%s'
				WHERE `id` = %d LIMIT 1
	                ",
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

		$update_photo = (($r[0]['avatar-date'] < datetime_convert('','','now -14 days')) ? true : false);

		// check that we have all the photos, this has been known to fail on occasion

		if((! $r[0]['photo']) || (! $r[0]['thumb']) || (! $r[0]['micro']) || ($update_photo)) {
			require_once("Photo.php");

			$photos = import_profile_photo($contact->image->url, $uid, $r[0]['id']);

			q("UPDATE `contact` SET `photo` = '%s',
                                        `thumb` = '%s',
                                        `micro` = '%s',
                                        `name-date` = '%s',
                                        `uri-date` = '%s',
                                        `avatar-date` = '%s'
                                        WHERE `id` = %d LIMIT 1
                                ",
			dbesc($photos[0]),
			dbesc($photos[1]),
			dbesc($photos[2]),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			intval($r[0]['id'])
			);
		}

	}

	return($r[0]["id"]);
}

function pumpio_dodelete(&$a, $uid, $self, $post, $own_id) {

	// Two queries for speed issues
	$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($post->object->id),
				intval($uid)
		);

	if (count($r))
		return drop_item($r[0]["id"], $false);

	$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($post->object->id),
				intval($uid)
		);

	if (count($r))
		return drop_item($r[0]["id"], $false);
}

function pumpio_dopost(&$a, $client, $uid, $self, $post, $own_id, $threadcompletion = false) {
	require_once('include/items.php');

	if (($post->verb == "like") OR ($post->verb == "favorite"))
		return pumpio_dolike($a, $uid, $self, $post, $own_id);

	if (($post->verb == "unlike") OR ($post->verb == "unfavorite"))
		return pumpio_dounlike($a, $uid, $self, $post, $own_id);

	if ($post->verb == "delete")
		return pumpio_dodelete($a, $uid, $self, $post, $own_id);

	if ($post->verb != "update") {
		// Two queries for speed issues
		$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($post->object->id),
					intval($uid)
			);

		if (count($r))
			return false;

		$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($post->object->id),
					intval($uid)
			);

		if (count($r))
			return false;
	}

	// Only handle these three types
	if (!strstr("post|share|update", $post->verb))
		return false;

	$receiptians = array();
	if (@is_array($post->cc))
		$receiptians = array_merge($receiptians, $post->cc);

	if (@is_array($post->to))
		$receiptians = array_merge($receiptians, $post->to);

	foreach ($receiptians AS $receiver)
		if (is_string($receiver->objectType))
			if ($receiver->id == "http://activityschema.org/collection/public")
				$public = true;

	$postarray = array();
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['uri'] = $post->object->id;

	if ($post->object->objectType != "comment") {
		$contact_id = pumpio_get_contact($uid, $post->actor);

		if (!$contact_id)
			$contact_id = $self[0]['id'];

		$postarray['parent-uri'] = $post->object->id;
	} else {
		$contact_id = 0;

		if(link_compare($post->actor->url, $own_id)) {
			$contact_id = $self[0]['id'];
			$post->actor->displayName = $self[0]['name'];
			$post->actor->url = $self[0]['url'];
			$post->actor->image->url = $self[0]['photo'];
		} else {
			// Take an existing contact, the contact of the note or - as a fallback - the id of the user
			$r = q("SELECT * FROM `contact` WHERE `url` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
				dbesc($post->actor->url),
				intval($uid)
			);

			if(count($r))
				$contact_id = $r[0]['id'];
			else {
				$r = q("SELECT * FROM `contact` WHERE `url` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
					dbesc($post->actor->url),
					intval($uid)
				);

				if(count($r))
					$contact_id = $r[0]['id'];
				else
					$contact_id = $self[0]['id'];
			}
		}

		$reply->verb = "note";
		$reply->cc = $post->cc;
		$reply->to = $post->to;
		$reply->object->objectType = $post->object->inReplyTo->objectType;
		$reply->object->content = $post->object->inReplyTo->content;
		$reply->object->id = $post->object->inReplyTo->id;
		$reply->actor = $post->object->inReplyTo->author;
		$reply->url = $post->object->inReplyTo->url;
		$reply->generator->displayName = "pumpio";
		$reply->published = $post->object->inReplyTo->published;
		$reply->received = $post->object->inReplyTo->updated;
		$reply->url = $post->object->inReplyTo->url;
		pumpio_dopost($a, $client, $uid, $self, $reply, $own_id);

		$postarray['parent-uri'] = $post->object->inReplyTo->id;
	}

	if ($post->object->pump_io->proxyURL)
		$postarray['extid'] = $post->object->pump_io->proxyURL;

	$postarray['contact-id'] = $contact_id;
	$postarray['verb'] = ACTIVITY_POST;
	$postarray['owner-name'] = $post->actor->displayName;
	$postarray['owner-link'] = $post->actor->url;
	$postarray['owner-avatar'] = $post->actor->image->url;
	$postarray['author-name'] = $post->actor->displayName;
	$postarray['author-link'] = $post->actor->url;
	$postarray['author-avatar'] = $post->actor->image->url;
	$postarray['plink'] = $post->object->url;
	$postarray['app'] = $post->generator->displayName;
	$postarray['body'] = html2bbcode($post->object->content);

	if ($post->object->fullImage->url != "")
		$postarray["body"] = "[url=".$post->object->fullImage->url."][img]".$post->object->image->url."[/img][/url]\n".$postarray["body"];

	if ($post->object->displayName != "")
		$postarray['title'] = $post->object->displayName;

	$postarray['created'] = datetime_convert('UTC','UTC',$post->published);
	$postarray['edited'] = datetime_convert('UTC','UTC',$post->received);
	if (!$public) {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self[0]['id'] . '>';
	}

	if ($post->verb == "share") {
		$postarray['body'] = "[share author='".$post->object->author->displayName.
                                "' profile='".$post->object->author->url.
                                "' avatar='".$post->object->author->image->url.
                                "' link='".$post->links->self->href."']".$postarray['body']."[/share]";
	}

	if (trim($postarray['body']) == "")
		return false;

	$top_item = item_store($postarray);

	if (($top_item == 0) AND ($post->verb == "update")) {
		$r = q("UPDATE `item` SET `title` = '%s', `body` = '%s' , `changed` = '%s' WHERE `uri` = '%s' AND `uid` = %d",
			dbesc($postarray["title"]),
			dbesc($postarray["body"]),
			dbesc($postarray["edited"]),
			dbesc($postarray["uri"]),
			intval($uid)
			);
	}

	if ($post->object->objectType == "comment") {

		if ($threadcompletion)
			pumpio_fetchallcomments($a, $uid, $postarray['parent-uri']);

		$user = q("SELECT * FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1",
				intval($uid)
			);

		if(!count($user))
			return $top_item;

		$importer_url = $a->get_baseurl() . '/profile/' . $user[0]['nickname'];

		if (link_compare($own_id, $postarray['author-link']))
			return $top_item;

		$myconv = q("SELECT `author-link`, `author-avatar`, `parent` FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `parent` != 0 AND `deleted` = 0",
				dbesc($postarray['parent-uri']),
				intval($uid)
				);

		if(count($myconv)) {

			foreach($myconv as $conv) {
				// now if we find a match, it means we're in this conversation

				if(!link_compare($conv['author-link'],$importer_url) AND !link_compare($conv['author-link'],$own_id))
					continue;

				require_once('include/enotify.php');

				$conv_parent = $conv['parent'];

				notification(array(
					'type'         => NOTIFY_COMMENT,
					'notify_flags' => $user[0]['notify-flags'],
					'language'     => $user[0]['language'],
					'to_name'      => $user[0]['username'],
					'to_email'     => $user[0]['email'],
					'uid'          => $user[0]['uid'],
					'item'         => $postarray,
					'link'             => $a->get_baseurl() . '/display/' . $user[0]['nickname'] . '/' . $top_item,
					'source_name'  => $postarray['author-name'],
					'source_link'  => $postarray['author-link'],
					'source_photo' => $postarray['author-avatar'],
					'verb'         => ACTIVITY_POST,
					'otype'        => 'item',
					'parent'       => $conv_parent,
					));

				// only send one notification
				break;
			}
		}
	}

	return $top_item;
}

function pumpio_fetchinbox(&$a, $uid) {

        $ckey    = get_pconfig($uid, 'pumpio', 'consumer_key');
        $csecret = get_pconfig($uid, 'pumpio', 'consumer_secret');
        $otoken  = get_pconfig($uid, 'pumpio', 'oauth_token');
        $osecret = get_pconfig($uid, 'pumpio', 'oauth_token_secret');
        $lastdate = get_pconfig($uid, 'pumpio', 'lastdate');
        $hostname = get_pconfig($uid, 'pumpio','host');
        $username = get_pconfig($uid, "pumpio", "user");

	$own_id = "https://".$hostname."/".$username;

	$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

        $client = new oauth_client_class;
        $client->oauth_version = '1.0a';
        $client->authorization_header = true;
        $client->url_parameters = false;

        $client->client_id = $ckey;
        $client->client_secret = $csecret;
        $client->access_token = $otoken;
        $client->access_token_secret = $osecret;

	$last_id = get_pconfig($uid,'pumpio','last_id');

	$url = 'https://'.$hostname.'/api/user/'.$username.'/inbox';

	if ($last_id != "")
		$url .= '?since='.urlencode($last_id);

        $success = $client->CallAPI($url, 'GET', array(), array('FailOnAccessError'=>true), $user);

	if ($user->items) {
	    $posts = array_reverse($user->items);

	    if (count($posts))
		    foreach ($posts as $post) {
			    $last_id = $post->id;
			    pumpio_dopost($a, $client, $uid, $self, $post, $own_id);
		    }
	}

	set_pconfig($uid,'pumpio','last_id', $last_id);
}

function pumpio_getallusers(&$a, $uid) {
        $ckey    = get_pconfig($uid, 'pumpio', 'consumer_key');
        $csecret = get_pconfig($uid, 'pumpio', 'consumer_secret');
        $otoken  = get_pconfig($uid, 'pumpio', 'oauth_token');
        $osecret = get_pconfig($uid, 'pumpio', 'oauth_token_secret');
        $hostname = get_pconfig($uid, 'pumpio','host');
        $username = get_pconfig($uid, "pumpio", "user");

        $client = new oauth_client_class;
        $client->oauth_version = '1.0a';
        $client->authorization_header = true;
        $client->url_parameters = false;

        $client->client_id = $ckey;
        $client->client_secret = $csecret;
        $client->access_token = $otoken;
        $client->access_token_secret = $osecret;

	$url = 'https://'.$hostname.'/api/user/'.$username.'/following';

        $success = $client->CallAPI($url, 'GET', array(), array('FailOnAccessError'=>true), $users);

	if ($users->totalItems > count($users->items)) {
		$url = 'https://'.$hostname.'/api/user/'.$username.'/following?count='.$users->totalItems;

	        $success = $client->CallAPI($url, 'GET', array(), array('FailOnAccessError'=>true), $users);
	}

	foreach ($users->items AS $user)
		echo pumpio_get_contact($uid, $user)."\n";
}

function pumpio_queue_hook(&$a,&$b) {

	$qi = q("SELECT * FROM `queue` WHERE `network` = '%s'",
		dbesc(NETWORK_PUMPIO)
	);
	if(! count($qi))
		return;

	require_once('include/queue_fn.php');

	foreach($qi as $x) {
		if($x['network'] !== NETWORK_PUMPIO)
			continue;

		logger('pumpio_queue: run');

		$r = q("SELECT `user`.* FROM `user` LEFT JOIN `contact` on `contact`.`uid` = `user`.`uid` 
			WHERE `contact`.`self` = 1 AND `contact`.`id` = %d LIMIT 1",
			intval($x['cid'])
		);
		if(! count($r))
			continue;

		$userdata = $r[0];

		//logger('pumpio_queue: fetching userdata '.print_r($userdata, true));

		$oauth_token = get_pconfig($userdata['uid'], "pumpio", "oauth_token");
		$oauth_token_secret = get_pconfig($userdata['uid'], "pumpio", "oauth_token_secret");
		$consumer_key = get_pconfig($userdata['uid'], "pumpio","consumer_key");
		$consumer_secret = get_pconfig($userdata['uid'], "pumpio","consumer_secret");

		$host = get_pconfig($userdata['uid'], "pumpio", "host");
		$user = get_pconfig($userdata['uid'], "pumpio", "user");

		$success = false;

		if ($oauth_token AND $oauth_token_secret AND
			$consumer_key AND $consumer_secret) {
			$username = $user.'@'.$host;

			logger('pumpio_queue: able to post for user '.$username);

			$z = unserialize($x['content']);

			$client = new oauth_client_class;
			$client->oauth_version = '1.0a';
			$client->url_parameters = false;
			$client->authorization_header = true;
			$client->access_token = $oauth_token;
			$client->access_token_secret = $oauth_token_secret;
			$client->client_id = $consumer_key;
			$client->client_secret = $consumer_secret;

			$success = $client->CallAPI($z['url'], 'POST', $z['post'], array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);

			if($success) {
				$post_id = $user->object->id;
				logger('pumpio_queue: send '.$username.': success '.$post_id);
				if($post_id AND $iscomment) {
					logger('pumpio_send '.$username.': Update extid '.$post_id." for post id ".$z['item']);
					q("UPDATE `item` SET `extid` = '%s' WHERE `id` = %d LIMIT 1",
						dbesc($post_id),
						intval($z['item'])
					);
				}
				remove_queue_item($x['id']);
			} else
				logger('pumpio_queue: send '.$username.': '.$url.' general error: ' . print_r($user,true));
		} else
			logger("pumpio_queue: Error getting tokens for user ".$userdata['uid']);

		if (!$success) {
			logger('pumpio_queue: delayed');
			update_queue_time($x['id']);
		}
	}
}

function pumpio_getreceiver(&$a, $b) {

	$receiver = array();

	if (!$b["private"]) {

		if(! strstr($b['postopts'],'pumpio'))
			return $receiver;

		$public = get_pconfig($b['uid'], "pumpio", "public");

                if ($public)
			$receiver["to"][] = Array(
						"objectType" => "collection",
						"id" => "http://activityschema.org/collection/public");
	} else {
		$cids = explode("><", $b["allow_cid"]);
		$gids = explode("><", $b["allow_gid"]);

		foreach ($cids AS $cid) {
			$cid = trim($cid, " <>");

			$r = q("SELECT `name`, `nick`, `url` FROM `contact` WHERE `id` = %d AND `uid` = %d AND `network` = '%s' AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
				intval($cid),
				intval($b["uid"]),
				dbesc(NETWORK_PUMPIO)
				);

			if (count($r)) {
				$receiver["bcc"][] = Array(
							"displayName" => $r[0]["name"],
							"objectType" => "person",
							"preferredUsername" => $r[0]["nick"],
							"url" => $r[0]["url"]);
			}
		}
		foreach ($gids AS $gid) {
			$gid = trim($gid, " <>");

			$r = q("SELECT `contact`.`name`, `contact`.`nick`, `contact`.`url`, `contact`.`network` ".
				"FROM `group_member`, `contact` WHERE `group_member`.`gid` = %d AND `group_member`.`uid` = %d ".
				"AND `contact`.`id` = `group_member`.`contact-id` AND `contact`.`network` = '%s'",
					intval($gid),
					intval($b["uid"]),
					dbesc(NETWORK_PUMPIO)
				);

			foreach ($r AS $row)
				$receiver["bcc"][] = Array(
							"displayName" => $row["name"],
							"objectType" => "person",
							"preferredUsername" => $row["nick"],
							"url" => $row["url"]);
		}
	}

	if ($b["inform"] != "") {

		$inform = explode(",", $b["inform"]);

		foreach ($inform AS $cid) {
			if (substr($cid, 0, 4) != "cid:")
				continue;

			$cid = str_replace("cid:", "", $cid);

			$r = q("SELECT `name`, `nick`, `url` FROM `contact` WHERE `id` = %d AND `uid` = %d AND `network` = '%s' AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
				intval($cid),
				intval($b["uid"]),
				dbesc(NETWORK_PUMPIO)
				);

			if (count($r)) {
					$receiver["to"][] = Array(
								"displayName" => $r[0]["name"],
								"objectType" => "person",
								"preferredUsername" => $r[0]["nick"],
								"url" => $r[0]["url"]);
			}
		}
	}

	return $receiver;
}

function pumpio_fetchallcomments(&$a, $uid, $id) {
	$ckey    = get_pconfig($uid, 'pumpio', 'consumer_key');
	$csecret = get_pconfig($uid, 'pumpio', 'consumer_secret');
	$otoken  = get_pconfig($uid, 'pumpio', 'oauth_token');
	$osecret = get_pconfig($uid, 'pumpio', 'oauth_token_secret');
	$hostname = get_pconfig($uid, 'pumpio','host');
	$username = get_pconfig($uid, "pumpio", "user");

	$own_id = "https://".$hostname."/".$username;

	logger("pumpio_fetchallcomments: completing comment for user ".$uid." url ".$url);

	$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	// Fetching the original post - Two queries for speed issues
	$r = q("SELECT extid FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
			dbesc($url),
			intval($uid)
		);

	if (!count($r)) {
		$r = q("SELECT extid FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($url),
				intval($uid)
			);

		if (!count($r))
			return false;
	}

	if ($r[0]["extid"])
		$url = $r[0]["extid"];
	else
		$url = $id;

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	logger("pumpio_fetchallcomments: fetching comment for user ".$uid." url ".$url);

	$success = $client->CallAPI($url, 'GET', array(), array('FailOnAccessError'=>true), $item);

	if (!$success)
		return;

	if ($item->replies->totalItems == 0)
		return;

	foreach ($item->replies->items AS $item) {
		if ($item->id == $id)
			continue;

		// Checking if the comment already exists - Two queries for speed issues
		$r = q("SELECT extid FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($url),
				intval($uid)
			);

		if (count($r))
			continue;

		$r = q("SELECT extid FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($url),
				intval($uid)
			);

		if (count($r))
			continue;

		$post->verb = "post";
		$post->actor = $item->author;
		$post->published = $item->published;
		$post->received = $item->updated;
		$post->generator->displayName = "pumpio";

		unset($item->author);
		unset($item->published);
		unset($item->updated);

		$post->object = $item;

		logger("pumpio_fetchallcomments: posting comment ".$post->object->id);
		pumpio_dopost($a, $client, $uid, $self, $post, $own_id, false);
	}
}

/*
Bugs:
 - refresh after post doesn't always happen

To-Do:
 - edit own notes
 - delete own notes

*/
