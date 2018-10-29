<?php
/**
 * Name: Buffer Post Connector
 * Description: Post to Buffer (Facebook, Google+, LinkedIn, Twitter)
 * Version: 0.2
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */
require 'addon/buffer/bufferapp.php';

use Friendica\App;
use Friendica\Content\Text;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;
use Friendica\Core\Protocol;
use Friendica\Database\DBA;
use Friendica\Model\ItemContent;
use Friendica\Util\Proxy as ProxyUtils;

function buffer_install()
{
	Addon::registerHook('post_local',           'addon/buffer/buffer.php', 'buffer_post_local');
	Addon::registerHook('notifier_normal',      'addon/buffer/buffer.php', 'buffer_send');
	Addon::registerHook('jot_networks',         'addon/buffer/buffer.php', 'buffer_jot_nets');
	Addon::registerHook('connector_settings',      'addon/buffer/buffer.php', 'buffer_settings');
	Addon::registerHook('connector_settings_post', 'addon/buffer/buffer.php', 'buffer_settings_post');
}

function buffer_uninstall()
{
	Addon::unregisterHook('post_local',              'addon/buffer/buffer.php', 'buffer_post_local');
	Addon::unregisterHook('notifier_normal',         'addon/buffer/buffer.php', 'buffer_send');
	Addon::unregisterHook('jot_networks',            'addon/buffer/buffer.php', 'buffer_jot_nets');
	Addon::unregisterHook('connector_settings',      'addon/buffer/buffer.php', 'buffer_settings');
	Addon::unregisterHook('connector_settings_post', 'addon/buffer/buffer.php', 'buffer_settings_post');
}

function buffer_module()
{
}

function buffer_content(App $a)
{
	if (! local_user()) {
		notice(L10n::t('Permission denied.') . EOL);
		return '';
	}

	require_once "mod/settings.php";
	settings_init($a);

	if (isset($a->argv[1])) {
		switch ($a->argv[1]) {
			case "connect":
				$o = buffer_connect($a);
				break;

			default:
				$o = print_r($a->argv, true);
				break;
		}
	} else {
		$o = buffer_connect($a);
	}

	return $o;
}

function buffer_addon_admin(App $a, &$o)
{
	$t = Text::getMarkupTemplate("admin.tpl", "addon/buffer/");

	$o = Text::replaceMacros($t, [
		'$submit' => L10n::t('Save Settings'),
		// name, label, value, help, [extra values]
		'$client_id' => ['client_id', L10n::t('Client ID'), Config::get('buffer', 'client_id'), ''],
		'$client_secret' => ['client_secret', L10n::t('Client Secret'), Config::get('buffer', 'client_secret'), ''],
	]);
}

function buffer_addon_admin_post(App $a)
{
	$client_id     = ((!empty($_POST['client_id']))     ? Text::noTags(trim($_POST['client_id']))     : '');
	$client_secret = ((!empty($_POST['client_secret'])) ? Text::noTags(trim($_POST['client_secret'])) : '');

	Config::set('buffer', 'client_id'    , $client_id);
	Config::set('buffer', 'client_secret', $client_secret);

	info(L10n::t('Settings updated.'). EOL);
}

function buffer_connect(App $a)
{
	if (isset($_REQUEST["error"])) {
		$o = L10n::t('Error when registering buffer connection:')." ".$_REQUEST["error"];
		return $o;
	}

	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Define the needed keys
	$client_id = Config::get('buffer','client_id');
	$client_secret = Config::get('buffer','client_secret');

	// The callback URL is the script that gets called after the user authenticates with buffer
	$callback_url = $a->getBaseURL()."/buffer/connect";

	$buffer = new BufferApp($client_id, $client_secret, $callback_url);

	if (!$buffer->ok) {
		$o .= '<a href="' . $buffer->get_login_url() . '">Connect to Buffer!</a>';
	} else {
		Text::logger("buffer_connect: authenticated");
		$o .= L10n::t("You are now authenticated to buffer. ");
		$o .= '<br /><a href="' . $a->getBaseURL() . '/settings/connectors">' . L10n::t("return to the connector page") . '</a>';
		PConfig::set(local_user(), 'buffer','access_token', $buffer->access_token);
	}

	return $o;
}

function buffer_jot_nets(App $a, &$b)
{
	if (!local_user()) {
		return;
	}

	$buffer_post = PConfig::get(local_user(), 'buffer', 'post');

	if (intval($buffer_post) == 1) {
		$buffer_defpost = PConfig::get(local_user(), 'buffer', 'post_by_default');
		$selected = ((intval($buffer_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="buffer_enable"' . $selected . ' value="1" /> '
		    . L10n::t('Post to Buffer') . '</div>';
	}
}

function buffer_settings(App $a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/buffer/buffer.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = PConfig::get(local_user(),'buffer','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = PConfig::get(local_user(),'buffer','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_buffer_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_buffer_expanded\'); openClose(\'settings_buffer_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/buffer.png" /><h3 class="connector">'. L10n::t('Buffer Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_buffer_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_buffer_expanded\'); openClose(\'settings_buffer_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/buffer.png" /><h3 class="connector">'. L10n::t('Buffer Export').'</h3>';
	$s .= '</span>';

	$client_id = Config::get("buffer", "client_id");
	$client_secret = Config::get("buffer", "client_secret");
	$access_token = PConfig::get(local_user(), "buffer", "access_token");

	$s .= '<div id="buffer-password-wrapper">';

	if ($access_token == "") {
		$s .= '<div id="buffer-authenticate-wrapper">';
		$s .= '<a href="'.$a->getBaseURL().'/buffer/connect">'.L10n::t("Authenticate your Buffer connection").'</a>';
		$s .= '</div><div class="clear"></div>';
	} else {
		$s .= '<div id="buffer-enable-wrapper">';
		$s .= '<label id="buffer-enable-label" for="buffer-checkbox">' . L10n::t('Enable Buffer Post Addon') . '</label>';
		$s .= '<input id="buffer-checkbox" type="checkbox" name="buffer" value="1" ' . $checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="buffer-bydefault-wrapper">';
		$s .= '<label id="buffer-bydefault-label" for="buffer-bydefault">' . L10n::t('Post to Buffer by default') . '</label>';
		$s .= '<input id="buffer-bydefault" type="checkbox" name="buffer_bydefault" value="1" ' . $def_checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="buffer-delete-wrapper">';
		$s .= '<label id="buffer-delete-label" for="buffer-delete">' . L10n::t('Check to delete this preset') . '</label>';
		$s .= '<input id="buffer-delete" type="checkbox" name="buffer_delete" value="1" />';
		$s .= '</div><div class="clear"></div>';

		// The callback URL is the script that gets called after the user authenticates with buffer
		$callback_url = $a->getBaseURL() . '/buffer/connect';

		$buffer = new BufferApp($client_id, $client_secret, $callback_url, $access_token);

		$profiles = $buffer->go('/profiles');
		if (is_array($profiles)) {
			$s .= '<div id="buffer-accounts-wrapper">';
			$s .= L10n::t("Posts are going to all accounts that are enabled by default:");
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

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="buffer-submit" name="buffer-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';
}


function buffer_settings_post(App $a, array &$b)
{
	if (!empty($_POST['buffer-submit'])) {
		if (!empty($_POST['buffer_delete'])) {
			PConfig::set(local_user(), 'buffer', 'access_token'   , '');
			PConfig::set(local_user(), 'buffer', 'post'           , false);
			PConfig::set(local_user(), 'buffer', 'post_by_default', false);
		} else {
			PConfig::set(local_user(), 'buffer', 'post'           , intval($_POST['buffer']));
			PConfig::set(local_user(), 'buffer', 'post_by_default', intval($_POST['buffer_bydefault']));
		}
	}
}

function buffer_post_local(App $a, array &$b)
{
	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	$buffer_post   = intval(PConfig::get(local_user(),'buffer','post'));

	$buffer_enable = (($buffer_post && !empty($_REQUEST['buffer_enable'])) ? intval($_REQUEST['buffer_enable']) : 0);

	if ($b['api_source'] && intval(PConfig::get(local_user(),'buffer','post_by_default'))) {
		$buffer_enable = 1;
	}

	if (!$buffer_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'buffer';
}

function buffer_send(App $a, array &$b)
{
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (!strstr($b['postopts'],'buffer')) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	// Dont't post if the post doesn't belong to us.
	// This is a check for forum postings
	$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
	if ($b['contact-id'] != $self['id']) {
		return;
	}

	// if post comes from buffer don't send it back
	//if($b['app'] == "Buffer")
	//	return;

	$client_id = Config::get("buffer", "client_id");
	$client_secret = Config::get("buffer", "client_secret");
	$access_token = PConfig::get($b['uid'], "buffer","access_token");
	$callback_url = "";

	if ($access_token) {
		$buffer = new BufferApp($client_id, $client_secret, $callback_url, $access_token);

		$profiles = $buffer->go('/profiles');
		if (is_array($profiles)) {
			Text::logger("Will send these parameter ".print_r($b, true), LOGGER_DEBUG);

			foreach ($profiles as $profile) {
				if (!$profile->default)
					continue;

				$send = false;

				switch ($profile->service) {
					case 'facebook':
						$send = ($b["extid"] != Protocol::FACEBOOK);
						$limit = 0;
						$markup = false;
						$includedlinks = false;
						$htmlmode = 9;
						break;

					case 'google':
						$send = ($b["extid"] != Protocol::GPLUS);
						$limit = 0;
						$markup = true;
						$includedlinks = false;
						$htmlmode = 9;
						break;

					case 'twitter':
						$send = ($b["extid"] != Protocol::TWITTER);
						$limit = 280;
						$markup = false;
						$includedlinks = true;
						$htmlmode = 8;
						break;

					case 'linkedin':
						$send = ($b["extid"] != Protocol::LINKEDIN);
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
					if ($item["title"] != "") {
						$item["title"] = "*" . $item["title"] . "*";
					}

					$item["body"] = preg_replace("(\[b\](.*?)\[\/b\])ism", '*$1*', $item["body"]);
					$item["body"] = preg_replace("(\[i\](.*?)\[\/i\])ism", '_$1_', $item["body"]);
					$item["body"] = preg_replace("(\[s\](.*?)\[\/s\])ism", '-$1-', $item["body"]);
				}

				$post = ItemContent::getPlaintextPost($item, $limit, $includedlinks, $htmlmode);
				Text::logger("buffer_send: converted message ".$b["id"]." result: ".print_r($post, true), LOGGER_DEBUG);

				// The image proxy is used as a sanitizer. Buffer seems to be really picky about pictures
				if (isset($post["image"])) {
					$post["image"] = ProxyUtils::proxifyUrl($post["image"]);
				}

				if (isset($post["preview"])) {
					$post["preview"] = ProxyUtils::proxifyUrl($post["preview"]);
				}

				// Seems like a bug to me
				// Buffer doesn't add links to Twitter (but pictures)
				if (($profile->service == "twitter") && isset($post["url"]) && ($post["type"] != "photo")) {
					$post["text"] .= " " . $post["url"];
				} elseif ($profile->service == "google") {
					$post["text"] .= html_entity_decode("&#x00A0;", ENT_QUOTES, 'UTF-8'); // Send a special blank to identify the post through the "fromgplus" addon
				}

				$message = [];
				$message["text"] = $post["text"];
				$message["profile_ids[]"] = $profile->id;
				$message["shorten"] = false;
				$message["now"] = true;

				if (isset($post["title"])) {
					$message["media[title]"] = $post["title"];
				}

				if (isset($post["description"])) {
					$message["media[description]"] = $post["description"];
				}

				if (isset($post["url"]) && ($post["type"] != "photo")) {
					$message["media[link]"] = $post["url"];
				}

				if (isset($post["image"])) {
					$message["media[picture]"] = $post["image"];

					if ($post["type"] == "photo") {
						$message["media[thumbnail]"] = $post["image"];
					}
				}

				if (isset($post["preview"])) {
					$message["media[thumbnail]"] = $post["preview"];
				}

				//print_r($message);
				Text::logger("buffer_send: data for message " . $b["id"] . ": " . print_r($message, true), LOGGER_DEBUG);
				$ret = $buffer->go('/updates/create', $message);
				Text::logger("buffer_send: send message " . $b["id"] . " result: " . print_r($ret, true), LOGGER_DEBUG);
			}
		}
	}
}
