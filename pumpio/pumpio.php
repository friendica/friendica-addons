<?php
/**
 * Name: pump.io Post Connector
 * Description: Bidirectional (posting, relaying and reading) connector for pump.io.
 * Version: 0.2
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\HTML;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Core\Protocol;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\Model\Contact;
use Friendica\Model\GContact;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\Queue;
use Friendica\Model\User;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;
use Friendica\Util\Strings;
use Friendica\Util\XML;

require 'addon/pumpio/oauth/http.php';
require 'addon/pumpio/oauth/oauth_client.php';
require_once "mod/share.php";

define('PUMPIO_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function pumpio_install()
{
	Hook::register('load_config',          'addon/pumpio/pumpio.php', 'pumpio_load_config');
	Hook::register('hook_fork',            'addon/pumpio/pumpio.php', 'hook_fork');
	Hook::register('post_local',           'addon/pumpio/pumpio.php', 'pumpio_post_local');
	Hook::register('notifier_normal',      'addon/pumpio/pumpio.php', 'pumpio_send');
	Hook::register('jot_networks',         'addon/pumpio/pumpio.php', 'pumpio_jot_nets');
	Hook::register('connector_settings',      'addon/pumpio/pumpio.php', 'pumpio_settings');
	Hook::register('connector_settings_post', 'addon/pumpio/pumpio.php', 'pumpio_settings_post');
	Hook::register('cron', 'addon/pumpio/pumpio.php', 'pumpio_cron');
	Hook::register('queue_predeliver', 'addon/pumpio/pumpio.php', 'pumpio_queue_hook');
	Hook::register('check_item_notification', 'addon/pumpio/pumpio.php', 'pumpio_check_item_notification');
}

function pumpio_uninstall()
{
	Hook::unregister('load_config',      'addon/pumpio/pumpio.php', 'pumpio_load_config');
	Hook::unregister('hook_fork',        'addon/pumpio/pumpio.php', 'pumpio_hook_fork');
	Hook::unregister('post_local',       'addon/pumpio/pumpio.php', 'pumpio_post_local');
	Hook::unregister('notifier_normal',  'addon/pumpio/pumpio.php', 'pumpio_send');
	Hook::unregister('jot_networks',     'addon/pumpio/pumpio.php', 'pumpio_jot_nets');
	Hook::unregister('connector_settings',      'addon/pumpio/pumpio.php', 'pumpio_settings');
	Hook::unregister('connector_settings_post', 'addon/pumpio/pumpio.php', 'pumpio_settings_post');
	Hook::unregister('cron', 'addon/pumpio/pumpio.php', 'pumpio_cron');
	Hook::unregister('queue_predeliver', 'addon/pumpio/pumpio.php', 'pumpio_queue_hook');
	Hook::unregister('check_item_notification', 'addon/pumpio/pumpio.php', 'pumpio_check_item_notification');
}

function pumpio_module() {}

function pumpio_content(App $a)
{
	if (!local_user()) {
		notice(L10n::t('Permission denied.') . EOL);
		return '';
	}

	require_once("mod/settings.php");
	settings_init($a);

	if (isset($a->argv[1])) {
		switch ($a->argv[1]) {
			case "connect":
				$o = pumpio_connect($a);
				break;
			default:
				$o = print_r($a->argv, true);
				break;
		}
	} else {
		$o = pumpio_connect($a);
	}
	return $o;
}

function pumpio_check_item_notification($a, &$notification_data)
{
	$hostname = PConfig::get($notification_data["uid"], 'pumpio', 'host');
	$username = PConfig::get($notification_data["uid"], "pumpio", "user");

	$notification_data["profiles"][] = "https://".$hostname."/".$username;
}

function pumpio_registerclient(App $a, $host)
{
	$url = "https://".$host."/api/client/register";

	$params = [];

	$application_name  = Config::get('pumpio', 'application_name');

	if ($application_name == "") {
		$application_name = $a->getHostName();
	}

	$adminlist = explode(",", str_replace(" ", "", Config::get('config', 'admin_email')));

	$params["type"] = "client_associate";
	$params["contacts"] = $adminlist[0];
	$params["application_type"] = "native";
	$params["application_name"] = $application_name;
	$params["logo_url"] = $a->getBaseURL()."/images/friendica-256.png";
	$params["redirect_uris"] = $a->getBaseURL()."/pumpio/connect";

	Logger::log("pumpio_registerclient: ".$url." parameters ".print_r($params, true), Logger::DEBUG);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_USERAGENT, "Friendica");

	$s = curl_exec($ch);
	$curl_info = curl_getinfo($ch);

	if ($curl_info["http_code"] == "200") {
		$values = json_decode($s);
		Logger::log("pumpio_registerclient: success ".print_r($values, true), Logger::DEBUG);
		return $values;
	}
	Logger::log("pumpio_registerclient: failed: ".print_r($curl_info, true), Logger::DEBUG);
	return false;

}

function pumpio_connect(App $a)
{
	// Define the needed keys
	$consumer_key = PConfig::get(local_user(), 'pumpio', 'consumer_key');
	$consumer_secret = PConfig::get(local_user(), 'pumpio', 'consumer_secret');
	$hostname = PConfig::get(local_user(), 'pumpio', 'host');

	if ((($consumer_key == "") || ($consumer_secret == "")) && ($hostname != "")) {
		Logger::log("pumpio_connect: register client");
		$clientdata = pumpio_registerclient($a, $hostname);
		PConfig::set(local_user(), 'pumpio', 'consumer_key', $clientdata->client_id);
		PConfig::set(local_user(), 'pumpio', 'consumer_secret', $clientdata->client_secret);

		$consumer_key = PConfig::get(local_user(), 'pumpio', 'consumer_key');
		$consumer_secret = PConfig::get(local_user(), 'pumpio', 'consumer_secret');

		Logger::log("pumpio_connect: ckey: ".$consumer_key." csecrect: ".$consumer_secret, Logger::DEBUG);
	}

	if (($consumer_key == "") || ($consumer_secret == "")) {
		Logger::log("pumpio_connect: ".sprintf("Unable to register the client at the pump.io server '%s'.", $hostname));

		$o .= L10n::t("Unable to register the client at the pump.io server '%s'.", $hostname);
		return $o;
	}

	// The callback URL is the script that gets called after the user authenticates with pumpio
	$callback_url = $a->getBaseURL()."/pumpio/connect";

	// Let's begin.  First we need a Request Token.  The request token is required to send the user
	// to pumpio's login page.

	// Create a new instance of the oauth_client_class library.  For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$client = new oauth_client_class;
	$client->debug = 0;
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
				Logger::log("pumpio_connect: otoken: ".$client->access_token." osecrect: ".$client->access_token_secret, Logger::DEBUG);
				PConfig::set(local_user(), "pumpio", "oauth_token", $client->access_token);
				PConfig::set(local_user(), "pumpio", "oauth_token_secret", $client->access_token_secret);
			}
		}
		$success = $client->Finalize($success);
	}
	if ($client->exit)  {
		$o = 'Could not connect to pumpio. Refresh the page or try again later.';
	}

	if ($success) {
		Logger::log("pumpio_connect: authenticated");
		$o = L10n::t("You are now authenticated to pumpio.");
		$o .= '<br /><a href="'.$a->getBaseURL().'/settings/connectors">'.L10n::t("return to the connector page").'</a>';
	} else {
		Logger::log("pumpio_connect: could not connect");
		$o = 'Could not connect to pumpio. Refresh the page or try again later.';
	}

	return $o;
}

function pumpio_jot_nets(App $a, &$b)
{
	if (! local_user()) {
		return;
	}

	$pumpio_post = PConfig::get(local_user(), 'pumpio', 'post');

	if (intval($pumpio_post) == 1) {
		$pumpio_defpost = PConfig::get(local_user(), 'pumpio', 'post_by_default');
		$selected = ((intval($pumpio_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="pumpio_enable"' . $selected . ' value="1" /> '
			. L10n::t('Post to pumpio') . '</div>';
	}
}

function pumpio_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/pumpio/pumpio.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$import_enabled = PConfig::get(local_user(), 'pumpio', 'import');
	$import_checked = (($import_enabled) ? ' checked="checked" ' : '');

	$enabled = PConfig::get(local_user(), 'pumpio', 'post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = PConfig::get(local_user(), 'pumpio', 'post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$public_enabled = PConfig::get(local_user(), 'pumpio', 'public');
	$public_checked = (($public_enabled) ? ' checked="checked" ' : '');

	$mirror_enabled = PConfig::get(local_user(), 'pumpio', 'mirror');
	$mirror_checked = (($mirror_enabled) ? ' checked="checked" ' : '');

	$servername = PConfig::get(local_user(), "pumpio", "host");
	$username = PConfig::get(local_user(), "pumpio", "user");

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_pumpio_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_pumpio_expanded\'); openClose(\'settings_pumpio_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/pumpio.png" /><h3 class="connector">'. L10n::t('Pump.io Import/Export/Mirror').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_pumpio_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_pumpio_expanded\'); openClose(\'settings_pumpio_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/pumpio.png" /><h3 class="connector">'. L10n::t('Pump.io Import/Export/Mirror').'</h3>';
	$s .= '</span>';

	$s .= '<div id="pumpio-username-wrapper">';
	$s .= '<label id="pumpio-username-label" for="pumpio-username">'.L10n::t('pump.io username (without the servername)').'</label>';
	$s .= '<input id="pumpio-username" type="text" name="pumpio_user" value="'.$username.'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="pumpio-servername-wrapper">';
	$s .= '<label id="pumpio-servername-label" for="pumpio-servername">'.L10n::t('pump.io servername (without "http://" or "https://" )').'</label>';
	$s .= '<input id="pumpio-servername" type="text" name="pumpio_host" value="'.$servername.'" />';
	$s .= '</div><div class="clear"></div>';

	if (($username != '') && ($servername != '')) {
		$oauth_token = PConfig::get(local_user(), "pumpio", "oauth_token");
		$oauth_token_secret = PConfig::get(local_user(), "pumpio", "oauth_token_secret");

		$s .= '<div id="pumpio-password-wrapper">';
		if (($oauth_token == "") || ($oauth_token_secret == "")) {
			$s .= '<div id="pumpio-authenticate-wrapper">';
			$s .= '<a href="'.$a->getBaseURL().'/pumpio/connect">'.L10n::t("Authenticate your pump.io connection").'</a>';
			$s .= '</div><div class="clear"></div>';
		} else {
			$s .= '<div id="pumpio-import-wrapper">';
			$s .= '<label id="pumpio-import-label" for="pumpio-import">' . L10n::t('Import the remote timeline') . '</label>';
			$s .= '<input id="pumpio-import" type="checkbox" name="pumpio_import" value="1" ' . $import_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-enable-wrapper">';
			$s .= '<label id="pumpio-enable-label" for="pumpio-checkbox">' . L10n::t('Enable pump.io Post Addon') . '</label>';
			$s .= '<input id="pumpio-checkbox" type="checkbox" name="pumpio" value="1" ' . $checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-bydefault-wrapper">';
			$s .= '<label id="pumpio-bydefault-label" for="pumpio-bydefault">' . L10n::t('Post to pump.io by default') . '</label>';
			$s .= '<input id="pumpio-bydefault" type="checkbox" name="pumpio_bydefault" value="1" ' . $def_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-public-wrapper">';
			$s .= '<label id="pumpio-public-label" for="pumpio-public">' . L10n::t('Should posts be public?') . '</label>';
			$s .= '<input id="pumpio-public" type="checkbox" name="pumpio_public" value="1" ' . $public_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-mirror-wrapper">';
			$s .= '<label id="pumpio-mirror-label" for="pumpio-mirror">' . L10n::t('Mirror all public posts') . '</label>';
			$s .= '<input id="pumpio-mirror" type="checkbox" name="pumpio_mirror" value="1" ' . $mirror_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-delete-wrapper">';
			$s .= '<label id="pumpio-delete-label" for="pumpio-delete">' . L10n::t('Check to delete this preset') . '</label>';
			$s .= '<input id="pumpio-delete" type="checkbox" name="pumpio_delete" value="1" />';
			$s .= '</div><div class="clear"></div>';
		}

		$s .= '</div><div class="clear"></div>';
	}

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="pumpio-submit" name="pumpio-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';
}

function pumpio_settings_post(App $a, array &$b)
{
	if (!empty($_POST['pumpio-submit'])) {
		if (!empty($_POST['pumpio_delete'])) {
			PConfig::set(local_user(), 'pumpio', 'consumer_key'      , '');
			PConfig::set(local_user(), 'pumpio', 'consumer_secret'   , '');
			PConfig::set(local_user(), 'pumpio', 'oauth_token'       , '');
			PConfig::set(local_user(), 'pumpio', 'oauth_token_secret', '');
			PConfig::set(local_user(), 'pumpio', 'post'              , false);
			PConfig::set(local_user(), 'pumpio', 'import'            , false);
			PConfig::set(local_user(), 'pumpio', 'host'              , '');
			PConfig::set(local_user(), 'pumpio', 'user'              , '');
			PConfig::set(local_user(), 'pumpio', 'public'            , false);
			PConfig::set(local_user(), 'pumpio', 'mirror'            , false);
			PConfig::set(local_user(), 'pumpio', 'post_by_default'   , false);
			PConfig::set(local_user(), 'pumpio', 'lastdate'          , 0);
			PConfig::set(local_user(), 'pumpio', 'last_id'           , '');
		} else {
			// filtering the username if it is filled wrong
			$user = $_POST['pumpio_user'];
			if (strstr($user, "@")) {
				$pos = strpos($user, "@");

				if ($pos > 0) {
					$user = substr($user, 0, $pos);
				}
			}

			// Filtering the hostname if someone is entering it with "http"
			$host = $_POST['pumpio_host'];
			$host = trim($host);
			$host = str_replace(["https://", "http://"], ["", ""], $host);

			PConfig::set(local_user(), 'pumpio', 'post'           , defaults($_POST, 'pumpio', false));
			PConfig::set(local_user(), 'pumpio', 'import'         , defaults($_POST, 'pumpio_import', false));
			PConfig::set(local_user(), 'pumpio', 'host'           , $host);
			PConfig::set(local_user(), 'pumpio', 'user'           , $user);
			PConfig::set(local_user(), 'pumpio', 'public'         , defaults($_POST, 'pumpio_public', false));
			PConfig::set(local_user(), 'pumpio', 'mirror'         , defaults($_POST, 'pumpio_mirror', false));
			PConfig::set(local_user(), 'pumpio', 'post_by_default', defaults($_POST, 'pumpio_bydefault', false));

			if (!empty($_POST['pumpio_mirror'])) {
				PConfig::delete(local_user(), 'pumpio', 'lastdate');
			}
		}
	}
}

function pumpio_load_config(App $a, Config\Cache\ConfigCacheLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('pumpio'));
}

function pumpio_hook_fork(App $a, array &$b)
{
        if ($b['name'] != 'notifier_normal') {
                return;
        }

        $post = $b['data'];

        // Deleting and editing is not supported by the addon (deleting could, but isn't by now)
        if ($post['deleted'] || ($post['created'] !== $post['edited'])) {
                $b['execute'] = false;
                return;
        }

        // if post comes from pump.io don't send it back
	if ($post['app'] == "pump.io") {
                $b['execute'] = false;
                return;
        }

        if (PConfig::get($post['uid'], 'pumpio', 'import')) {
                // Don't fork if it isn't a reply to a pump.io post
                if (($post['parent'] != $post['id']) && !Item::exists(['id' => $post['parent'], 'network' => Protocol::PUMPIO])) {
                        Logger::log('No pump.io parent found for item ' . $post['id']);
                        $b['execute'] = false;
                        return;
                }
        } else {
                // Comments are never exported when we don't import the pumpio timeline
                if (!strstr($post['postopts'], 'pumpio') || ($post['parent'] != $post['id']) || $post['private']) {
                        $b['execute'] = false;
                        return;
                }
        }
}

function pumpio_post_local(App $a, array &$b)
{
	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	$pumpio_post   = intval(PConfig::get(local_user(), 'pumpio', 'post'));

	$pumpio_enable = (($pumpio_post && !empty($_REQUEST['pumpio_enable'])) ? intval($_REQUEST['pumpio_enable']) : 0);

	if ($b['api_source'] && intval(PConfig::get(local_user(), 'pumpio', 'post_by_default'))) {
		$pumpio_enable = 1;
	}

	if (!$pumpio_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'pumpio';
}

function pumpio_send(App $a, array &$b)
{
	if (!PConfig::get($b["uid"], 'pumpio', 'import') && ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))) {
		return;
	}

	Logger::log("pumpio_send: parameter ".print_r($b, true), Logger::DATA);

	if ($b['parent'] != $b['id']) {
		// Looking if its a reply to a pumpio post
		$condition = ['id' => $b['parent'], 'network' => Protocol::PUMPIO];
		$orig_post = Item::selectFirst([], $condition);

		if (!DBA::isResult($orig_post)) {
			Logger::log("pumpio_send: no pumpio post ".$b["parent"]);
			return;
		} else {
			$iscomment = true;
		}
	} else {
		$iscomment = false;

		$receiver = pumpio_getreceiver($a, $b);

		Logger::log("pumpio_send: receiver ".print_r($receiver, true));

		if (!count($receiver) && ($b['private'] || !strstr($b['postopts'], 'pumpio'))) {
			return;
		}

		// Dont't post if the post doesn't belong to us.
		// This is a check for forum postings
		$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
		if ($b['contact-id'] != $self['id']) {
			return;
		}
	}

	if ($b['verb'] == ACTIVITY_LIKE) {
		if ($b['deleted']) {
			pumpio_action($a, $b["uid"], $b["thr-parent"], "unlike");
		} else {
			pumpio_action($a, $b["uid"], $b["thr-parent"], "like");
		}
		return;
	}

	if ($b['verb'] == ACTIVITY_DISLIKE) {
		return;
	}

	if (($b['verb'] == ACTIVITY_POST) && ($b['created'] !== $b['edited']) && !$b['deleted']) {
		pumpio_action($a, $b["uid"], $b["uri"], "update", $b["body"]);
	}

	if (($b['verb'] == ACTIVITY_POST) && $b['deleted']) {
		pumpio_action($a, $b["uid"], $b["uri"], "delete");
	}

	if ($b['deleted'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	// if post comes from pump.io don't send it back
	if ($b['app'] == "pump.io") {
		return;
	}

	// To-Do;
	// Support for native shares
	// http://<hostname>/api/<type>/shares?id=<the-object-id>

	$oauth_token = PConfig::get($b['uid'], "pumpio", "oauth_token");
	$oauth_token_secret = PConfig::get($b['uid'], "pumpio", "oauth_token_secret");
	$consumer_key = PConfig::get($b['uid'], "pumpio","consumer_key");
	$consumer_secret = PConfig::get($b['uid'], "pumpio","consumer_secret");

	$host = PConfig::get($b['uid'], "pumpio", "host");
	$user = PConfig::get($b['uid'], "pumpio", "user");
	$public = PConfig::get($b['uid'], "pumpio", "public");

	if ($oauth_token && $oauth_token_secret) {
		$title = trim($b['title']);

		$content = BBCode::convert($b['body'], false, 4);

		$params = [];

		$params["verb"] = "post";

		if (!$iscomment) {
			$params["object"] = [
				'objectType' => "note",
				'content' => $content];

			if (!empty($title)) {
				$params["object"]["displayName"] = $title;
			}

			if (!empty($receiver["to"])) {
				$params["to"] = $receiver["to"];
			}

			if (!empty($receiver["bto"])) {
				$params["bto"] = $receiver["bto"];
			}

			if (!empty($receiver["cc"])) {
				$params["cc"] = $receiver["cc"];
			}

			if (!empty($receiver["bcc"])) {
				$params["bcc"] = $receiver["bcc"];
			}
		 } else {
			$inReplyTo = ["id" => $orig_post["uri"],
				"objectType" => "note"];

			if (($orig_post["object-type"] != "") && (strstr($orig_post["object-type"], NAMESPACE_ACTIVITY_SCHEMA))) {
				$inReplyTo["objectType"] = str_replace(NAMESPACE_ACTIVITY_SCHEMA, '', $orig_post["object-type"]);
			}

			$params["object"] = [
				'objectType' => "comment",
				'content' => $content,
				'inReplyTo' => $inReplyTo];

			if ($title != "") {
				$params["object"]["displayName"] = $title;
			}
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

		if (pumpio_reachable($url)) {
			$success = $client->CallAPI($url, 'POST', $params, ['FailOnAccessError'=>true, 'RequestContentType'=>'application/json'], $user);
		} else {
			$success = false;
		}

		if ($success) {
			if ($user->generator->displayName) {
				PConfig::set($b["uid"], "pumpio", "application_name", $user->generator->displayName);
			}

			$post_id = $user->object->id;
			Logger::log('pumpio_send '.$username.': success '.$post_id);
			if ($post_id && $iscomment) {
				Logger::log('pumpio_send '.$username.': Update extid '.$post_id." for post id ".$b['id']);
				Item::update(['extid' => $post_id], ['id' => $b['id']]);
			}
		} else {
			Logger::log('pumpio_send '.$username.': '.$url.' general error: ' . print_r($user, true));

			$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", $b['uid']);
			if (DBA::isResult($r)) {
				$a->contact = $r[0]["id"];
			}

			$s = serialize(['url' => $url, 'item' => $b['id'], 'post' => $params]);

			Queue::add($a->contact, Protocol::PUMPIO, $s);
			notice(L10n::t('Pump.io post failed. Queued for retry.').EOL);
		}
	}
}

function pumpio_action(App $a, $uid, $uri, $action, $content = "")
{
	// Don't do likes and other stuff if you don't import the timeline
	if (!PConfig::get($uid, 'pumpio', 'import')) {
		return;
	}

	$ckey    = PConfig::get($uid, 'pumpio', 'consumer_key');
	$csecret = PConfig::get($uid, 'pumpio', 'consumer_secret');
	$otoken  = PConfig::get($uid, 'pumpio', 'oauth_token');
	$osecret = PConfig::get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = PConfig::get($uid, 'pumpio', 'host');
	$username = PConfig::get($uid, "pumpio", "user");

	$orig_post = Item::selectFirst([], ['uri' => $uri, 'uid' => $uid]);

	if (!DBA::isResult($orig_post)) {
		return;
	}

	if ($orig_post["extid"] && !strstr($orig_post["extid"], "/proxy/")) {
		$uri = $orig_post["extid"];
	} else {
		$uri = $orig_post["uri"];
	}

	if (($orig_post["object-type"] != "") && (strstr($orig_post["object-type"], NAMESPACE_ACTIVITY_SCHEMA))) {
		$objectType = str_replace(NAMESPACE_ACTIVITY_SCHEMA, '', $orig_post["object-type"]);
	} elseif (strstr($uri, "/api/comment/")) {
		$objectType = "comment";
	} elseif (strstr($uri, "/api/note/")) {
		$objectType = "note";
	} elseif (strstr($uri, "/api/image/")) {
		$objectType = "image";
	}

	$params["verb"] = $action;
	$params["object"] = ['id' => $uri,
				"objectType" => $objectType,
				"content" => $content];

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$url = 'https://'.$hostname.'/api/user/'.$username.'/feed';

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'POST', $params, ['FailOnAccessError'=>true, 'RequestContentType'=>'application/json'], $user);
	} else {
		$success = false;
	}

	if ($success) {
		Logger::log('pumpio_action '.$username.' '.$action.': success '.$uri);
	} else {
		Logger::log('pumpio_action '.$username.' '.$action.': general error: '.$uri.' '.print_r($user, true));

		$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", $uid);
		if (DBA::isResult($r)) {
			$a->contact = $r[0]["id"];
		}

		$s = serialize(['url' => $url, 'item' => $orig_post["id"], 'post' => $params]);

		Queue::add($a->contact, Protocol::PUMPIO, $s);
		notice(L10n::t('Pump.io like failed. Queued for retry.').EOL);
	}
}

function pumpio_sync(App $a)
{
	$r = q("SELECT * FROM `addon` WHERE `installed` = 1 AND `name` = 'pumpio'");

	if (!DBA::isResult($r)) {
		return;
	}

	$last = Config::get('pumpio', 'last_poll');

	$poll_interval = intval(Config::get('pumpio', 'poll_interval', PUMPIO_DEFAULT_POLL_INTERVAL));

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			Logger::log('pumpio: poll intervall not reached');
			return;
		}
	}
	Logger::log('pumpio: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'pumpio' AND `k` = 'mirror' AND `v` = '1' ORDER BY RAND() ");
	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			Logger::log('pumpio: mirroring user '.$rr['uid']);
			pumpio_fetchtimeline($a, $rr['uid']);
		}
	}

	$abandon_days = intval(Config::get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'pumpio' AND `k` = 'import' AND `v` = '1' ORDER BY RAND() ");
	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			if ($abandon_days != 0) {
				$user = q("SELECT `login_date` FROM `user` WHERE uid=%d AND `login_date` >= '%s'", $rr['uid'], $abandon_limit);
				if (!DBA::isResult($user)) {
					Logger::log('abandoned account: timeline from user '.$rr['uid'].' will not be imported');
					continue;
				}
			}

			Logger::log('pumpio: importing timeline from user '.$rr['uid']);
			pumpio_fetchinbox($a, $rr['uid']);

			// check for new contacts once a day
			$last_contact_check = PConfig::get($rr['uid'], 'pumpio', 'contact_check');
			if ($last_contact_check) {
				$next_contact_check = $last_contact_check + 86400;
			} else {
				$next_contact_check = 0;
			}

			if ($next_contact_check <= time()) {
				pumpio_getallusers($a, $rr["uid"]);
				PConfig::set($rr['uid'], 'pumpio', 'contact_check', time());
			}
		}
	}

	Logger::log('pumpio: cron_end');

	Config::set('pumpio', 'last_poll', time());
}

function pumpio_cron(App $a, $b)
{
	Worker::add(PRIORITY_MEDIUM,"addon/pumpio/pumpio_sync.php");
}

function pumpio_fetchtimeline(App $a, $uid)
{
	$ckey    = PConfig::get($uid, 'pumpio', 'consumer_key');
	$csecret = PConfig::get($uid, 'pumpio', 'consumer_secret');
	$otoken  = PConfig::get($uid, 'pumpio', 'oauth_token');
	$osecret = PConfig::get($uid, 'pumpio', 'oauth_token_secret');
	$lastdate = PConfig::get($uid, 'pumpio', 'lastdate');
	$hostname = PConfig::get($uid, 'pumpio', 'host');
	$username = PConfig::get($uid, "pumpio", "user");

	//  get the application name for the pump.io app
	//  1st try personal config, then system config and fallback to the
	//  hostname of the node if neither one is set.
	$application_name  = PConfig::get($uid, 'pumpio', 'application_name');
	if ($application_name == "") {
		$application_name  = Config::get('pumpio', 'application_name');
	}
	if ($application_name == "") {
		$application_name = $a->getHostName();
	}

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

	Logger::log('pumpio: fetching for user '.$uid.' '.$url.' C:'.$client->client_id.' CS:'.$client->client_secret.' T:'.$client->access_token.' TS:'.$client->access_token_secret);

	$useraddr = $username.'@'.$hostname;

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError'=>true], $user);
	} else {
		$success = false;
		$user = [];
	}

	if (!$success) {
		Logger::log('pumpio: error fetching posts for user '.$uid." ".$useraddr." ".print_r($user, true));
		return;
	}

	$posts = array_reverse($user->items);

	$initiallastdate = $lastdate;
	$lastdate = '';

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->published <= $initiallastdate) {
				continue;
			}

			if ($lastdate < $post->published) {
				$lastdate = $post->published;
			}

			if ($first_time) {
				continue;
			}

			$receiptians = [];
			if (@is_array($post->cc)) {
				$receiptians = array_merge($receiptians, $post->cc);
			}

			if (@is_array($post->to)) {
				$receiptians = array_merge($receiptians, $post->to);
			}

			$public = false;
			foreach ($receiptians AS $receiver) {
				if (is_string($receiver->objectType) && ($receiver->id == "http://activityschema.org/collection/public")) {
					$public = true;
				}
			}

			if ($public && !stristr($post->generator->displayName, $application_name)) {
				$_SESSION["authenticated"] = true;
				$_SESSION["uid"] = $uid;

				unset($_REQUEST);
				$_REQUEST["api_source"] = true;
				$_REQUEST["profile_uid"] = $uid;
				$_REQUEST["source"] = "pump.io";

				if (isset($post->object->id)) {
					$_REQUEST['message_id'] = Protocol::PUMPIO.":".$post->object->id;
				}

				if ($post->object->displayName != "") {
					$_REQUEST["title"] = HTML::toBBCode($post->object->displayName);
				} else {
					$_REQUEST["title"] = "";
				}

				$_REQUEST["body"] = HTML::toBBCode($post->object->content);

				// To-Do: Picture has to be cached and stored locally
				if ($post->object->fullImage->url != "") {
					if ($post->object->fullImage->pump_io->proxyURL != "") {
						$_REQUEST["body"] = "[url=".$post->object->fullImage->pump_io->proxyURL."][img]".$post->object->image->pump_io->proxyURL."[/img][/url]\n".$_REQUEST["body"];
					} else {
						$_REQUEST["body"] = "[url=".$post->object->fullImage->url."][img]".$post->object->image->url."[/img][/url]\n".$_REQUEST["body"];
					}
				}

				Logger::log('pumpio: posting for user '.$uid);

				require_once('mod/item.php');

				item_post($a);
				Logger::log('pumpio: posting done - user '.$uid);
			}
		}
	}

	if ($lastdate != 0) {
		PConfig::set($uid, 'pumpio', 'lastdate', $lastdate);
	}
}

function pumpio_dounlike(App $a, $uid, $self, $post, $own_id)
{
	// Searching for the unliked post
	// Two queries for speed issues
	$orig_post = Item::selectFirst([], ['uri' => $post->object->id, 'uid' => $uid]);
	if (!DBA::isResult($orig_post)) {
		$orig_post = Item::selectFirst([], ['extid' => $post->object->id, 'uid' => $uid]);
		if (!DBA::isResult($orig_post)) {
			return;
		}
	}

	$contactid = 0;

	if (Strings::compareLink($post->actor->url, $own_id)) {
		$contactid = $self[0]['id'];
	} else {
		$r = q("SELECT * FROM `contact` WHERE `nurl` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
			DBA::escape(Strings::normaliseLink($post->actor->url)),
			intval($uid)
		);

		if (DBA::isResult($r)) {
			$contactid = $r[0]['id'];
		}

		if ($contactid == 0) {
			$contactid = $orig_post['contact-id'];
		}
	}

	Item::delete(['verb' => ACTIVITY_LIKE, 'uid' => $uid, 'contact-id' => $contactid, 'thr-parent' => $orig_post['uri']]);

	if (DBA::isResult($r)) {
		Logger::log("pumpio_dounlike: unliked existing like. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
	} else {
		Logger::log("pumpio_dounlike: not found. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
	}
}

function pumpio_dolike(App $a, $uid, $self, $post, $own_id, $threadcompletion = true)
{
	require_once('include/items.php');

	if (empty($post->object->id)) {
		Logger::log('Got empty like: '.print_r($post, true), Logger::DEBUG);
		return;
	}

	// Searching for the liked post
	// Two queries for speed issues
	$orig_post = Item::selectFirst([], ['uri' => $post->object->id, 'uid' => $uid]);
	if (!DBA::isResult($orig_post)) {
		$orig_post = Item::selectFirst([], ['extid' => $post->object->id, 'uid' => $uid]);
		if (!DBA::isResult($orig_post)) {
			return;
		}
	}

	// thread completion
	if ($threadcompletion) {
		pumpio_fetchallcomments($a, $uid, $post->object->id);
	}

	$contactid = 0;

	if (Strings::compareLink($post->actor->url, $own_id)) {
		$contactid = $self[0]['id'];
		$post->actor->displayName = $self[0]['name'];
		$post->actor->url = $self[0]['url'];
		$post->actor->image->url = $self[0]['photo'];
	} else {
		$r = q("SELECT * FROM `contact` WHERE `nurl` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
			DBA::escape(Strings::normaliseLink($post->actor->url)),
			intval($uid)
		);

		if (DBA::isResult($r)) {
			$contactid = $r[0]['id'];
		}

		if ($contactid == 0) {
			$contactid = $orig_post['contact-id'];
		}
	}

	$condition = ['verb' => ACTIVITY_LIKE, 'uid' => $uid, 'contact-id' => $contactid, 'thr-parent' => $orig_post['uri']];
	if (Item::exists($condition)) {
		Logger::log("pumpio_dolike: found existing like. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
		return;
	}

	$likedata = [];
	$likedata['parent'] = $orig_post['id'];
	$likedata['verb'] = ACTIVITY_LIKE;
	$likedata['gravity'] = GRAVITY_ACTIVITY;
	$likedata['uid'] = $uid;
	$likedata['wall'] = 0;
	$likedata['network'] = Protocol::PUMPIO;
	$likedata['uri'] = Item::newURI($uid);
	$likedata['parent-uri'] = $orig_post["uri"];
	$likedata['contact-id'] = $contactid;
	$likedata['app'] = $post->generator->displayName;
	$likedata['author-name'] = $post->actor->displayName;
	$likedata['author-link'] = $post->actor->url;
	if (!empty($post->actor->image)) {
		$likedata['author-avatar'] = $post->actor->image->url;
	}

	$author  = '[url=' . $likedata['author-link'] . ']' . $likedata['author-name'] . '[/url]';
	$objauthor =  '[url=' . $orig_post['author-link'] . ']' . $orig_post['author-name'] . '[/url]';
	$post_type = L10n::t('status');
	$plink = '[url=' . $orig_post['plink'] . ']' . $post_type . '[/url]';
	$likedata['object-type'] = ACTIVITY_OBJ_NOTE;

	$likedata['body'] = L10n::t('%1$s likes %2$s\'s %3$s', $author, $objauthor, $plink);

	$likedata['object'] = '<object><type>' . ACTIVITY_OBJ_NOTE . '</type><local>1</local>' .
		'<id>' . $orig_post['uri'] . '</id><link>' . XML::escape('<link rel="alternate" type="text/html" href="' . XML::escape($orig_post['plink']) . '" />') . '</link><title>' . $orig_post['title'] . '</title><content>' . $orig_post['body'] . '</content></object>';

	$ret = Item::insert($likedata);

	Logger::log("pumpio_dolike: ".$ret." User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
}

function pumpio_get_contact($uid, $contact, $no_insert = false)
{
	$gcontact = ["url" => $contact->url, "network" => Protocol::PUMPIO, "generation" => 2,
		"name" => $contact->displayName,  "hide" => true,
		"nick" => $contact->preferredUsername,
		"addr" => str_replace("acct:", "", $contact->id)];

	if (!empty($contact->location->displayName)) {
		$gcontact["location"] = $contact->location->displayName;
	}

	if (!empty($contact->summary)) {
		$gcontact["about"] = $contact->summary;
	}

	if (!empty($contact->image->url)) {
		$gcontact["photo"] = $contact->image->url;
	}

	GContact::update($gcontact);
	$cid = Contact::getIdForURL($contact->url, $uid);

	if ($no_insert) {
		return $cid;
	}

	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `nurl` = '%s' LIMIT 1",
		intval($uid), DBA::escape(Strings::normaliseLink($contact->url)));

	if (!DBA::isResult($r)) {
		// create contact record
		q("INSERT INTO `contact` (`uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
					`name`, `nick`, `photo`, `network`, `rel`, `priority`,
					`location`, `about`, `writable`, `blocked`, `readonly`, `pending` )
				VALUES (%d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, '%s', '%s', %d, 0, 0, 0)",
			intval($uid),
			DBA::escape(DateTimeFormat::utcNow()),
			DBA::escape($contact->url),
			DBA::escape(Strings::normaliseLink($contact->url)),
			DBA::escape(str_replace("acct:", "", $contact->id)),
			DBA::escape(''),
			DBA::escape($contact->id), // What is it for?
			DBA::escape('pump.io ' . $contact->id), // What is it for?
			DBA::escape($contact->displayName),
			DBA::escape($contact->preferredUsername),
			DBA::escape($contact->image->url),
			DBA::escape(Protocol::PUMPIO),
			intval(Contact::FRIEND),
			intval(1),
			DBA::escape($contact->location->displayName),
			DBA::escape($contact->summary),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `nurl` = '%s' AND `uid` = %d LIMIT 1",
			DBA::escape(Strings::normaliseLink($contact->url)),
			intval($uid)
			);

		if (!DBA::isResult($r)) {
			return false;
		}

		$contact_id = $r[0]['id'];

		Group::addMember(User::getDefaultGroup($uid), $contact_id);
	} else {
		$contact_id = $r[0]["id"];

		/*	if (DB_UPDATE_VERSION >= "1177")
				q("UPDATE `contact` SET `location` = '%s',
							`about` = '%s'
						WHERE `id` = %d",
					dbesc($contact->location->displayName),
					dbesc($contact->summary),
					intval($r[0]['id'])
				);
		*/
	}

	if (!empty($contact->image->url)) {
		Contact::updateAvatar($contact->image->url, $uid, $contact_id);
	}

	return $contact_id;
}

function pumpio_dodelete(App $a, $uid, $self, $post, $own_id)
{
	// Two queries for speed issues
	$condition = ['uri' => $post->object->id, 'uid' => $uid];
	if (Item::exists($condition)) {
		Item::delete($condition);
		return true;
	}

	$condition = ['extid' => $post->object->id, 'uid' => $uid];
	if (Item::exists($condition)) {
		Item::delete($condition);
		return true;
	}
	return false;
}

function pumpio_dopost(App $a, $client, $uid, $self, $post, $own_id, $threadcompletion = true)
{
	require_once('include/items.php');

	if (($post->verb == "like") || ($post->verb == "favorite")) {
		return pumpio_dolike($a, $uid, $self, $post, $own_id);
	}

	if (($post->verb == "unlike") || ($post->verb == "unfavorite")) {
		return pumpio_dounlike($a, $uid, $self, $post, $own_id);
	}

	if ($post->verb == "delete") {
		return pumpio_dodelete($a, $uid, $self, $post, $own_id);
	}

	if ($post->verb != "update") {
		// Two queries for speed issues
		if (Item::exists(['uri' => $post->object->id, 'uid' => $uid])) {
			return false;
		}
		if (Item::exists(['extid' => $post->object->id, 'uid' => $uid])) {
			return false;
		}
	}

	// Only handle these three types
	if (!strstr("post|share|update", $post->verb)) {
		return false;
	}

	$receiptians = [];
	if (@is_array($post->cc)) {
		$receiptians = array_merge($receiptians, $post->cc);
	}

	if (@is_array($post->to)) {
		$receiptians = array_merge($receiptians, $post->to);
	}

	$public = false;

	foreach ($receiptians AS $receiver) {
		if (is_string($receiver->objectType) && ($receiver->id == "http://activityschema.org/collection/public")) {
			$public = true;
		}
	}

	$postarray = [];
	$postarray['network'] = Protocol::PUMPIO;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['uri'] = $post->object->id;
	$postarray['object-type'] = NAMESPACE_ACTIVITY_SCHEMA.strtolower($post->object->objectType);

	if ($post->object->objectType != "comment") {
		$contact_id = pumpio_get_contact($uid, $post->actor);

		if (!$contact_id) {
			$contact_id = $self[0]['id'];
		}

		$postarray['parent-uri'] = $post->object->id;

		if (!$public) {
			$postarray['private'] = 1;
			$postarray['allow_cid'] = '<' . $self[0]['id'] . '>';
		}
	} else {
		$contact_id = pumpio_get_contact($uid, $post->actor, true);

		if (Strings::compareLink($post->actor->url, $own_id)) {
			$contact_id = $self[0]['id'];
			$post->actor->displayName = $self[0]['name'];
			$post->actor->url = $self[0]['url'];
			$post->actor->image->url = $self[0]['photo'];
		} elseif ($contact_id == 0) {
			// Take an existing contact, the contact of the note or - as a fallback - the id of the user
			$r = q("SELECT * FROM `contact` WHERE `nurl` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
				DBA::escape(Strings::normaliseLink($post->actor->url)),
				intval($uid)
			);

			if (DBA::isResult($r)) {
				$contact_id = $r[0]['id'];
			} else {
				$r = q("SELECT * FROM `contact` WHERE `nurl` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
					DBA::escape(Strings::normaliseLink($post->actor->url)),
					intval($uid)
				);

				if (DBA::isResult($r)) {
					$contact_id = $r[0]['id'];
				} else {
					$contact_id = $self[0]['id'];
				}
			}
		}

		$reply = new stdClass;
		$reply->verb = "note";

		if (isset($post->cc)) {
			$reply->cc = $post->cc;
		}

		if (isset($post->to)) {
			$reply->to = $post->to;
		}

		$reply->object = new stdClass;
		$reply->object->objectType = $post->object->inReplyTo->objectType;
		$reply->object->content = $post->object->inReplyTo->content;
		$reply->object->id = $post->object->inReplyTo->id;
		$reply->actor = $post->object->inReplyTo->author;
		$reply->url = $post->object->inReplyTo->url;
		$reply->generator = new stdClass;
		$reply->generator->displayName = "pumpio";
		$reply->published = $post->object->inReplyTo->published;
		$reply->received = $post->object->inReplyTo->updated;
		$reply->url = $post->object->inReplyTo->url;
		pumpio_dopost($a, $client, $uid, $self, $reply, $own_id, false);

		$postarray['parent-uri'] = $post->object->inReplyTo->id;
	}

	// When there is no content there is no need to continue
	if (empty($post->object->content)) {
		return false;
	}

	if (!empty($post->object->pump_io->proxyURL)) {
		$postarray['extid'] = $post->object->pump_io->proxyURL;
	}

	$postarray['contact-id'] = $contact_id;
	$postarray['verb'] = ACTIVITY_POST;
	$postarray['owner-name'] = $post->actor->displayName;
	$postarray['owner-link'] = $post->actor->url;
	$postarray['author-name'] = $postarray['owner-name'];
	$postarray['author-link'] = $postarray['owner-link'];
	if (!empty($post->actor->image)) {
		$postarray['owner-avatar'] = $post->actor->image->url;
		$postarray['author-avatar'] = $postarray['owner-avatar'];
	}
	$postarray['plink'] = $post->object->url;
	$postarray['app'] = $post->generator->displayName;
	$postarray['title'] = '';
	$postarray['body'] = HTML::toBBCode($post->object->content);
	$postarray['object'] = json_encode($post);

	if (!empty($post->object->fullImage->url)) {
		$postarray["body"] = "[url=".$post->object->fullImage->url."][img]".$post->object->image->url."[/img][/url]\n".$postarray["body"];
	}

	if (!empty($post->object->displayName)) {
		$postarray['title'] = $post->object->displayName;
	}

	$postarray['created'] = DateTimeFormat::utc($post->published);
	if (isset($post->updated)) {
		$postarray['edited'] = DateTimeFormat::utc($post->updated);
	} elseif (isset($post->received)) {
		$postarray['edited'] = DateTimeFormat::utc($post->received);
	} else {
		$postarray['edited'] = $postarray['created'];
	}

	if ($post->verb == "share") {
		if (isset($post->object->author->displayName) && ($post->object->author->displayName != "")) {
			$share_author = $post->object->author->displayName;
		} elseif (isset($post->object->author->preferredUsername) && ($post->object->author->preferredUsername != "")) {
			$share_author = $post->object->author->preferredUsername;
		} else {
			$share_author = $post->object->author->url;
		}

		if (isset($post->object->created)) {
			$created = DateTimeFormat::utc($post->object->created);
		} else {
			$created = '';
		}

		$postarray['body'] = share_header($share_author, $post->object->author->url,
						$post->object->author->image->url, "",
						$created, $post->links->self->href).
					$postarray['body']."[/share]";
	}

	if (trim($postarray['body']) == "") {
		return false;
	}

	$top_item = Item::insert($postarray);
	$postarray["id"] = $top_item;

	if (($top_item == 0) && ($post->verb == "update")) {
		$fields = ['title' => $postarray["title"], 'body' => $postarray["body"], 'changed' => $postarray["edited"]];
		$condition = ['uri' => $postarray["uri"], 'uid' => $uid];
		Item::update($fields, $condition);
	}

	if (($post->object->objectType == "comment") && $threadcompletion) {
		pumpio_fetchallcomments($a, $uid, $postarray['parent-uri']);
	}

	return $top_item;
}

function pumpio_fetchinbox(App $a, $uid)
{
	$ckey     = PConfig::get($uid, 'pumpio', 'consumer_key');
	$csecret  = PConfig::get($uid, 'pumpio', 'consumer_secret');
	$otoken   = PConfig::get($uid, 'pumpio', 'oauth_token');
	$osecret  = PConfig::get($uid, 'pumpio', 'oauth_token_secret');
	$lastdate = PConfig::get($uid, 'pumpio', 'lastdate');
	$hostname = PConfig::get($uid, 'pumpio', 'host');
	$username = PConfig::get($uid, "pumpio", "user");

	$own_id = "https://".$hostname."/".$username;

	$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	$lastitems = q("SELECT `uri` FROM `thread`
			INNER JOIN `item` ON `item`.`id` = `thread`.`iid`
			WHERE `thread`.`network` = '%s' AND `thread`.`uid` = %d AND `item`.`extid` != ''
			ORDER BY `thread`.`commented` DESC LIMIT 10",
				DBA::escape(Protocol::PUMPIO),
				intval($uid)
			);

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$last_id = PConfig::get($uid, 'pumpio', 'last_id');

	$url = 'https://'.$hostname.'/api/user/'.$username.'/inbox';

	if ($last_id != "") {
		$url .= '?since='.urlencode($last_id);
	}

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError'=>true], $user);
	} else {
		$success = false;
	}

	if (!$success) {
		return;
	}

	if (!empty($user->items)) {
		$posts = array_reverse($user->items);

		if (count($posts)) {
			foreach ($posts as $post) {
				$last_id = $post->id;
				pumpio_dopost($a, $client, $uid, $self, $post, $own_id, true);
			}
		}
	}

	foreach ($lastitems as $item) {
		pumpio_fetchallcomments($a, $uid, $item["uri"]);
	}

	PConfig::set($uid, 'pumpio', 'last_id', $last_id);
}

function pumpio_getallusers(App &$a, $uid)
{
	$ckey     = PConfig::get($uid, 'pumpio', 'consumer_key');
	$csecret  = PConfig::get($uid, 'pumpio', 'consumer_secret');
	$otoken   = PConfig::get($uid, 'pumpio', 'oauth_token');
	$osecret  = PConfig::get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = PConfig::get($uid, 'pumpio', 'host');
	$username = PConfig::get($uid, "pumpio", "user");

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$url = 'https://'.$hostname.'/api/user/'.$username.'/following';

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError' => true], $users);
	} else {
		$success = false;
	}

	if (empty($users)) {
		return;
	}

	if ($users->totalItems > count($users->items)) {
		$url = 'https://'.$hostname.'/api/user/'.$username.'/following?count='.$users->totalItems;

		if (pumpio_reachable($url)) {
			$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError' => true], $users);
		} else {
			$success = false;
		}
	}

	if (!empty($users->items)) {
		foreach ($users->items as $user) {
			pumpio_get_contact($uid, $user);
		}
	}
}

function pumpio_queue_hook(App $a, array &$b)
{
	$qi = q("SELECT * FROM `queue` WHERE `network` = '%s'",
		DBA::escape(Protocol::PUMPIO)
	);

	if (!DBA::isResult($qi)) {
		return;
	}

	foreach ($qi as $x) {
		if ($x['network'] !== Protocol::PUMPIO) {
			continue;
		}

		Logger::log('pumpio_queue: run');

		$r = q("SELECT `user`.* FROM `user` LEFT JOIN `contact` ON `contact`.`uid` = `user`.`uid`
			WHERE `contact`.`self` = 1 AND `contact`.`id` = %d LIMIT 1",
			intval($x['cid'])
		);
		if (!DBA::isResult($r)) {
			continue;
		}

		$userdata = $r[0];

		//Logger::log('pumpio_queue: fetching userdata '.print_r($userdata, true));

		$oauth_token        = PConfig::get($userdata['uid'], "pumpio", "oauth_token");
		$oauth_token_secret = PConfig::get($userdata['uid'], "pumpio", "oauth_token_secret");
		$consumer_key       = PConfig::get($userdata['uid'], "pumpio", "consumer_key");
		$consumer_secret    = PConfig::get($userdata['uid'], "pumpio", "consumer_secret");

		$host = PConfig::get($userdata['uid'], "pumpio", "host");
		$user = PConfig::get($userdata['uid'], "pumpio", "user");

		$success = false;

		if ($oauth_token && $oauth_token_secret &&
			$consumer_key && $consumer_secret) {
			$username = $user.'@'.$host;

			Logger::log('pumpio_queue: able to post for user '.$username);

			$z = unserialize($x['content']);

			$client = new oauth_client_class;
			$client->oauth_version = '1.0a';
			$client->url_parameters = false;
			$client->authorization_header = true;
			$client->access_token = $oauth_token;
			$client->access_token_secret = $oauth_token_secret;
			$client->client_id = $consumer_key;
			$client->client_secret = $consumer_secret;

			if (pumpio_reachable($z['url'])) {
				$success = $client->CallAPI($z['url'], 'POST', $z['post'], ['FailOnAccessError'=>true, 'RequestContentType'=>'application/json'], $user);
			} else {
				$success = false;
			}

			if ($success) {
				$post_id = $user->object->id;
				Logger::log('pumpio_queue: send '.$username.': success '.$post_id);
				if ($post_id && $iscomment) {
					Logger::log('pumpio_send '.$username.': Update extid '.$post_id." for post id ".$z['item']);
					Item::update(['extid' => $post_id], ['id' => $z['item']]);
				}
				Queue::removeItem($x['id']);
			} else {
				Logger::log('pumpio_queue: send '.$username.': '.$z['url'].' general error: ' . print_r($user, true));
			}
		} else {
			Logger::log("pumpio_queue: Error getting tokens for user ".$userdata['uid']);
		}

		if (!$success) {
			Logger::log('pumpio_queue: delayed');
			Queue::updateTime($x['id']);
		}
	}
}

function pumpio_getreceiver(App $a, array $b)
{
	$receiver = [];

	if (!$b["private"]) {
		if (!strstr($b['postopts'], 'pumpio')) {
			return $receiver;
		}

		$public = PConfig::get($b['uid'], "pumpio", "public");

		if ($public) {
			$receiver["to"][] = [
						"objectType" => "collection",
						"id" => "http://activityschema.org/collection/public"];
		}
	} else {
		$cids = explode("><", $b["allow_cid"]);
		$gids = explode("><", $b["allow_gid"]);

		foreach ($cids AS $cid) {
			$cid = trim($cid, " <>");

			$r = q("SELECT `name`, `nick`, `url` FROM `contact` WHERE `id` = %d AND `uid` = %d AND `network` = '%s' AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
				intval($cid),
				intval($b["uid"]),
				DBA::escape(Protocol::PUMPIO)
				);

			if (DBA::isResult($r)) {
				$receiver["bcc"][] = [
							"displayName" => $r[0]["name"],
							"objectType" => "person",
							"preferredUsername" => $r[0]["nick"],
							"url" => $r[0]["url"]];
			}
		}
		foreach ($gids AS $gid) {
			$gid = trim($gid, " <>");

			$r = q("SELECT `contact`.`name`, `contact`.`nick`, `contact`.`url`, `contact`.`network` ".
				"FROM `group_member`, `contact` WHERE `group_member`.`gid` = %d ".
				"AND `contact`.`id` = `group_member`.`contact-id` AND `contact`.`network` = '%s'",
					intval($gid),
					DBA::escape(Protocol::PUMPIO)
				);

			foreach ($r AS $row)
				$receiver["bcc"][] = [
							"displayName" => $row["name"],
							"objectType" => "person",
							"preferredUsername" => $row["nick"],
							"url" => $row["url"]];
		}
	}

	if ($b["inform"] != "") {
		$inform = explode(",", $b["inform"]);

		foreach ($inform AS $cid) {
			if (substr($cid, 0, 4) != "cid:") {
				continue;
			}

			$cid = str_replace("cid:", "", $cid);

			$r = q("SELECT `name`, `nick`, `url` FROM `contact` WHERE `id` = %d AND `uid` = %d AND `network` = '%s' AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
				intval($cid),
				intval($b["uid"]),
				DBA::escape(Protocol::PUMPIO)
				);

			if (DBA::isResult($r)) {
				$receiver["to"][] = [
					"displayName" => $r[0]["name"],
					"objectType" => "person",
					"preferredUsername" => $r[0]["nick"],
					"url" => $r[0]["url"]];
			}
		}
	}

	return $receiver;
}

function pumpio_fetchallcomments(App $a, $uid, $id)
{
	$ckey     = PConfig::get($uid, 'pumpio', 'consumer_key');
	$csecret  = PConfig::get($uid, 'pumpio', 'consumer_secret');
	$otoken   = PConfig::get($uid, 'pumpio', 'oauth_token');
	$osecret  = PConfig::get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = PConfig::get($uid, 'pumpio', 'host');
	$username = PConfig::get($uid, "pumpio", "user");

	Logger::log("pumpio_fetchallcomments: completing comment for user ".$uid." post id ".$id);

	$own_id = "https://".$hostname."/".$username;

	$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	// Fetching the original post
	$condition = ["`uri` = ? AND `uid` = ? AND `extid` != ''", $id, $uid];
	$item = Item::selectFirst(['extid'], $condition);
	if (!DBA::isResult($item)) {
		return false;
	}

	$url = $item["extid"];

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	Logger::log("pumpio_fetchallcomments: fetching comment for user ".$uid." url ".$url);

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError'=>true], $item);
	} else {
		$success = false;
	}

	if (!$success) {
		return;
	}

	if ($item->likes->totalItems != 0) {
		foreach ($item->likes->items AS $post) {
			$like = new stdClass;
			$like->object = new stdClass;
			$like->object->id = $item->id;
			$like->actor = new stdClass;
			if (!empty($item->displayName)) {
				$like->actor->displayName = $item->displayName;
			}
			//$like->actor->preferredUsername = $item->preferredUsername;
			//$like->actor->image = $item->image;
			$like->actor->url = $item->url;
			$like->generator = new stdClass;
			$like->generator->displayName = "pumpio";
			pumpio_dolike($a, $uid, $self, $post, $own_id, false);
		}
	}

	if ($item->replies->totalItems == 0) {
		return;
	}

	foreach ($item->replies->items AS $item) {
		if ($item->id == $id) {
			continue;
		}

		// Checking if the comment already exists - Two queries for speed issues
		if (Item::exists(['uri' => $item->id, 'uid' => $uid])) {
			continue;
		}

		if (Item::exists(['extid' => $item->id, 'uid' => $uid])) {
			continue;
		}

		$post = new stdClass;
		$post->verb = "post";
		$post->actor = $item->author;
		$post->published = $item->published;
		$post->received = $item->updated;
		$post->generator = new stdClass;
		$post->generator->displayName = "pumpio";
		// To-Do: Check for public post

		unset($item->author);
		unset($item->published);
		unset($item->updated);

		$post->object = $item;

		Logger::log("pumpio_fetchallcomments: posting comment ".$post->object->id." ".print_r($post, true));
		pumpio_dopost($a, $client, $uid, $self, $post, $own_id, false);
	}
}

function pumpio_reachable($url)
{
	return Network::curl($url, false, $redirects, ['timeout'=>10])->isSuccess();
}

/*
To-Do:
 - edit own notes
 - delete own notes
*/
