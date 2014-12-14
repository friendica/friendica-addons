<?php
/**
 * Name: Buffer Post Connector
 * Description: Post to Buffer (Linkedin, App.net, Google+, Facebook, Twitter)
 * Version: 0.2
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */
require('addon/buffer/bufferapp.php');

function buffer_install() {
	register_hook('post_local',           'addon/buffer/buffer.php', 'buffer_post_local');
	register_hook('notifier_normal',      'addon/buffer/buffer.php', 'buffer_send');
	register_hook('jot_networks',         'addon/buffer/buffer.php', 'buffer_jot_nets');
	register_hook('connector_settings',      'addon/buffer/buffer.php', 'buffer_settings');
	register_hook('connector_settings_post', 'addon/buffer/buffer.php', 'buffer_settings_post');
}

function buffer_uninstall() {
	unregister_hook('post_local',       'addon/buffer/buffer.php', 'buffer_post_local');
	unregister_hook('notifier_normal',  'addon/buffer/buffer.php', 'buffer_send');
	unregister_hook('jot_networks',     'addon/buffer/buffer.php', 'buffer_jot_nets');
	unregister_hook('connector_settings',      'addon/buffer/buffer.php', 'buffer_settings');
	unregister_hook('connector_settings_post', 'addon/buffer/buffer.php', 'buffer_settings_post');
}

function buffer_module() {}

function buffer_content(&$a) {

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return '';
	}

	require_once("mod/settings.php");
	settings_init($a);

	if (isset($a->argv[1]))
		switch ($a->argv[1]) {
			case "connect":
				$o = buffer_connect($a);
				break;
			default:
				$o = print_r($a->argv, true);
				break;
		}
	else
		$o = buffer_connect($a);

	return $o;
}

function buffer_plugin_admin(&$a, &$o){
	$t = get_markup_template( "admin.tpl", "addon/buffer/" );

	$o = replace_macros($t, array(
		'$submit' => t('Save Settings'),
								// name, label, value, help, [extra values]
		'$client_id' => array('client_id', t('Client ID'),  get_config('buffer', 'client_id' ), ''),
		'$client_secret' => array('client_secret', t('Client Secret'),  get_config('buffer', 'client_secret' ), ''),
	));
}
function buffer_plugin_admin_post(&$a){
        $client_id     =       ((x($_POST,'client_id'))              ? notags(trim($_POST['client_id']))   : '');
        $client_secret =       ((x($_POST,'client_secret'))   ? notags(trim($_POST['client_secret'])): '');
        set_config('buffer','client_id',$client_id);
        set_config('buffer','client_secret',$client_secret);
        info( t('Settings updated.'). EOL );
}

function buffer_connect(&$a) {

	if (isset($_REQUEST["error"])) {
		$o = t('Error when registering buffer connection:')." ".$_REQUEST["error"];
		return $o;
	}
	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Define the needed keys
	$client_id = get_config('buffer','client_id');
	$client_secret = get_config('buffer','client_secret');

	// The callback URL is the script that gets called after the user authenticates with buffer
	$callback_url = $a->get_baseurl()."/buffer/connect";

	$buffer = new BufferApp($client_id, $client_secret, $callback_url);

	if (!$buffer->ok) {
		$o .= '<a href="' . $buffer->get_login_url() . '">Connect to Buffer!</a>';
	} else {
		logger("buffer_connect: authenticated");
		$o .= t("You are now authenticated to buffer. ");
		$o .= '<br /><a href="'.$a->get_baseurl().'/settings/connectors">'.t("return to the connector page").'</a>';
		set_pconfig(local_user(), 'buffer','access_token', $buffer->access_token);
	}

	return($o);
}

function buffer_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$buffer_post = get_pconfig(local_user(),'buffer','post');
	if(intval($buffer_post) == 1) {
		$buffer_defpost = get_pconfig(local_user(),'buffer','post_by_default');
		$selected = ((intval($buffer_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="buffer_enable"' . $selected . ' value="1" /> '
		    . t('Post to Buffer') . '</div>';
	}
}

function buffer_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/buffer/buffer.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = get_pconfig(local_user(),'buffer','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = get_pconfig(local_user(),'buffer','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_buffer_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_buffer_expanded\'); openClose(\'settings_buffer_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/buffer.png" /><h3 class="connector">'. t('Buffer Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_buffer_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_buffer_expanded\'); openClose(\'settings_buffer_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/buffer.png" /><h3 class="connector">'. t('Buffer Export').'</h3>';
	$s .= '</span>';

	$client_id = get_config("buffer", "client_id");
	$client_secret = get_config("buffer", "client_secret");
	$access_token = get_pconfig(local_user(), "buffer", "access_token");

	$s .= '<div id="buffer-password-wrapper">';
	if ($access_token == "") {
		$s .= '<div id="buffer-authenticate-wrapper">';
		$s .= '<a href="'.$a->get_baseurl().'/buffer/connect">'.t("Authenticate your Buffer connection").'</a>';
		$s .= '</div><div class="clear"></div>';
	} else {
		$s .= '<div id="buffer-enable-wrapper">';
		$s .= '<label id="buffer-enable-label" for="buffer-checkbox">' . t('Enable Buffer Post Plugin') . '</label>';
		$s .= '<input id="buffer-checkbox" type="checkbox" name="buffer" value="1" ' . $checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="buffer-bydefault-wrapper">';
		$s .= '<label id="buffer-bydefault-label" for="buffer-bydefault">' . t('Post to Buffer by default') . '</label>';
		$s .= '<input id="buffer-bydefault" type="checkbox" name="buffer_bydefault" value="1" ' . $def_checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="buffer-delete-wrapper">';
		$s .= '<label id="buffer-delete-label" for="buffer-delete">' . t('Check to delete this preset') . '</label>';
		$s .= '<input id="buffer-delete" type="checkbox" name="buffer_delete" value="1" />';
		$s .= '</div><div class="clear"></div>';

		$buffer = new BufferApp($client_id, $client_secret, $callback_url, $access_token);

		$profiles = $buffer->go('/profiles');
		if (is_array($profiles)) {
			$s .= '<div id="buffer-accounts-wrapper">';
			$s .= t("Posts are going to all accounts that are enabled by default:");
			$s .= "<ul>";
			foreach ($profiles as $profile) {
				if (!$profile->default)
					continue;
				$s .= "<li>";
				//$s .= "<img src='".$profile->avatar_https."' width='16' />";
				$s .= " ".$profile->formatted_username." (".$profile->formatted_service.")";
				$s .= "</li>";
			}
			$s .= "</ul>";
			$s .= '</div><div class="clear"></div>';
		}

	}

	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="buffer-submit" name="buffer-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}


function buffer_settings_post(&$a,&$b) {

	if(x($_POST,'buffer-submit')) {
		if(x($_POST,'buffer_delete')) {
			set_pconfig(local_user(),'buffer','access_token','');
			set_pconfig(local_user(),'buffer','post',false);
			set_pconfig(local_user(),'buffer','post_by_default',false);
		} else {
			set_pconfig(local_user(),'buffer','post',intval($_POST['buffer']));
			set_pconfig(local_user(),'buffer','post_by_default',intval($_POST['buffer_bydefault']));
		}
	}
}

function buffer_post_local(&$a,&$b) {

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	$buffer_post   = intval(get_pconfig(local_user(),'buffer','post'));

	$buffer_enable = (($buffer_post && x($_REQUEST,'buffer_enable')) ? intval($_REQUEST['buffer_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'buffer','post_by_default')))
		$buffer_enable = 1;

	if(! $buffer_enable)
		return;

	if(strlen($b['postopts']))
		$b['postopts'] .= ',';

	$b['postopts'] .= 'buffer';
}

function buffer_send(&$a,&$b) {

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
		return;

	if(! strstr($b['postopts'],'buffer'))
		return;

	if($b['parent'] != $b['id'])
		return;

	// if post comes from buffer don't send it back
	//if($b['app'] == "Buffer")
	//	return;

	$client_id = get_config("buffer", "client_id");
	$client_secret = get_config("buffer", "client_secret");
	$access_token = get_pconfig($b['uid'], "buffer","access_token");

	if($access_token) {
		$buffer = new BufferApp($client_id, $client_secret, $callback_url, $access_token);

		require_once("include/plaintext.php");
		require_once("include/network.php");

		$profiles = $buffer->go('/profiles');
		if (is_array($profiles)) {
			logger("Will send these parameter ".print_r($b, true), LOGGER_DEBUG);

			foreach ($profiles as $profile) {
				if (!$profile->default)
					continue;

				$send = false;

				switch ($profile->service) {
					case 'appdotnet':
						$send = ($b["extid"] != NETWORK_APPNET);
						$limit = 256;
						$markup = false;
						$includedlinks = true;
						$htmlmode = 6;
						break;
					case 'facebook':
						$send = ($b["extid"] != NETWORK_FACEBOOK);
						$limit = 0;
						$markup = false;
						$includedlinks = false;
						$htmlmode = 9;
						break;
					case 'google':
						$send = ($b["extid"] != NETWORK_GPLUS);
						$limit = 0;
						$markup = true;
						$includedlinks = false;
						$htmlmode = 9;
						break;
					case 'twitter':
						$send = ($b["extid"] != NETWORK_TWITTER);
						$limit = 140;
						$markup = false;
						$includedlinks = true;
						$htmlmode = 8;
						break;
					case 'linkedin':
						$send = ($b["extid"] != NETWORK_LINKEDIN);
						$limit = 700;
						$markup = false;
						$includedlinks = true;
						$htmlmode = 2;
						break;
				}

				if (!$send)
					continue;

				$item = $b;

				// Markup for Google+
				if ($markup) {
					if  ($item["title"] != "")
						$item["title"] = "*".$item["title"]."*";

					$item["body"] = preg_replace("(\[b\](.*?)\[\/b\])ism",'*$1*',$item["body"]);
					$item["body"] = preg_replace("(\[i\](.*?)\[\/i\])ism",'_$1_',$item["body"]);
					$item["body"] = preg_replace("(\[s\](.*?)\[\/s\])ism",'-$1-',$item["body"]);
				}

				$post = plaintext($a, $item, $limit, $includedlinks, $htmlmode);
				logger("buffer_send: converted message ".$b["id"]." result: ".print_r($post, true), LOGGER_DEBUG);

				// The image proxy is used as a sanitizer. Buffer seems to be really picky about pictures
				require_once("mod/proxy.php");
				if (isset($post["image"]))
					$post["image"] = proxy_url($post["image"]);

				if (isset($post["preview"]))
					$post["preview"] = proxy_url($post["preview"]);

				//if ($profile->service == "twitter") {
				if ($includedlinks) {
					if (isset($post["url"]))
						$post["url"] = short_link($post["url"]);
					if (isset($post["image"]))
						$post["image"] = short_link($post["image"]);
					if (isset($post["preview"]))
						$post["preview"] = short_link($post["preview"]);
				}

				// Seems like a bug to me
				// Buffer doesn't add links to Twitter and App.net (but pictures)
				//if ($includedlinks AND isset($post["url"]))
				if (($profile->service == "twitter") AND isset($post["url"]))
					$post["text"] .= " ".$post["url"];
				elseif (($profile->service == "appdotnet") AND isset($post["url"]) AND isset($post["title"])) {
					$post["title"] = shortenmsg($post["title"], 90);
					$post["text"] = shortenmsg($post["text"], $limit - (24 + strlen($post["title"])));
					$post["text"] .= "\n[".$post["title"]."](".$post["url"].")";
				} elseif (($profile->service == "appdotnet") AND isset($post["url"]))
					$post["text"] .= " ".$post["url"];
				elseif ($profile->service == "google")
					$post["text"] .= html_entity_decode("&#x00A0;", ENT_QUOTES, 'UTF-8'); // Send a special blank to identify the post through the "fromgplus" addon

				$message = array();
				$message["text"] = $post["text"];
				$message["profile_ids[]"] = $profile->id;
				$message["shorten"] = false;
				$message["now"] = true;

				if (isset($post["title"]))
					$message["media[title]"] = $post["title"];

				if (isset($post["description"]))
					$message["media[description]"] = $post["description"];

				if (isset($post["url"]) AND ($post["type"] != "photo"))
					$message["media[link]"] = $post["url"];

				if (isset($post["image"])) {
					$message["media[picture]"] = $post["image"];
					if ($post["type"] == "photo")
						$message["media[thumbnail]"] = $post["image"];
				}

				if (isset($post["preview"]))
					$message["media[thumbnail]"] = $post["preview"];

				//print_r($message);
				$ret = $buffer->go('/updates/create', $message);
				logger("buffer_send: send message ".$b["id"]." result: ".print_r($ret, true), LOGGER_DEBUG);
			}
		}
	}
}
