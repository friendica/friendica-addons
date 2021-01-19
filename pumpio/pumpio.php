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
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Model\User;
use Friendica\Protocol\Activity;
use Friendica\Protocol\ActivityNamespace;
use Friendica\Util\ConfigFileLoader;
use Friendica\Util\DateTimeFormat;
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
	Hook::register('check_item_notification', 'addon/pumpio/pumpio.php', 'pumpio_check_item_notification');
}

function pumpio_module() {}

function pumpio_content(App $a)
{
	if (!local_user()) {
		notice(DI::l10n()->t('Permission denied.') . EOL);
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
	$hostname = DI::pConfig()->get($notification_data["uid"], 'pumpio', 'host');
	$username = DI::pConfig()->get($notification_data["uid"], "pumpio", "user");

	$notification_data["profiles"][] = "https://".$hostname."/".$username;
}

function pumpio_registerclient(App $a, $host)
{
	$url = "https://".$host."/api/client/register";

	$params = [];

	$application_name  = DI::config()->get('pumpio', 'application_name');

	if ($application_name == "") {
		$application_name = DI::baseUrl()->getHostname();
	}

	$adminlist = explode(",", str_replace(" ", "", DI::config()->get('config', 'admin_email')));

	$params["type"] = "client_associate";
	$params["contacts"] = $adminlist[0];
	$params["application_type"] = "native";
	$params["application_name"] = $application_name;
	$params["logo_url"] = DI::baseUrl()->get()."/images/friendica-256.png";
	$params["redirect_uris"] = DI::baseUrl()->get()."/pumpio/connect";

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
	$consumer_key = DI::pConfig()->get(local_user(), 'pumpio', 'consumer_key');
	$consumer_secret = DI::pConfig()->get(local_user(), 'pumpio', 'consumer_secret');
	$hostname = DI::pConfig()->get(local_user(), 'pumpio', 'host');

	if ((($consumer_key == "") || ($consumer_secret == "")) && ($hostname != "")) {
		Logger::log("pumpio_connect: register client");
		$clientdata = pumpio_registerclient($a, $hostname);
		DI::pConfig()->set(local_user(), 'pumpio', 'consumer_key', $clientdata->client_id);
		DI::pConfig()->set(local_user(), 'pumpio', 'consumer_secret', $clientdata->client_secret);

		$consumer_key = DI::pConfig()->get(local_user(), 'pumpio', 'consumer_key');
		$consumer_secret = DI::pConfig()->get(local_user(), 'pumpio', 'consumer_secret');

		Logger::log("pumpio_connect: ckey: ".$consumer_key." csecrect: ".$consumer_secret, Logger::DEBUG);
	}

	if (($consumer_key == "") || ($consumer_secret == "")) {
		Logger::log("pumpio_connect: ".sprintf("Unable to register the client at the pump.io server '%s'.", $hostname));

		return DI::l10n()->t("Unable to register the client at the pump.io server '%s'.", $hostname);
	}

	// The callback URL is the script that gets called after the user authenticates with pumpio
	$callback_url = DI::baseUrl()->get()."/pumpio/connect";

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
				DI::pConfig()->set(local_user(), "pumpio", "oauth_token", $client->access_token);
				DI::pConfig()->set(local_user(), "pumpio", "oauth_token_secret", $client->access_token_secret);
			}
		}
		$success = $client->Finalize($success);
	}
	if ($client->exit)  {
		$o = 'Could not connect to pumpio. Refresh the page or try again later.';
	}

	if ($success) {
		Logger::log("pumpio_connect: authenticated");
		$o = DI::l10n()->t("You are now authenticated to pumpio.");
		$o .= '<br /><a href="'.DI::baseUrl()->get().'/settings/connectors">'.DI::l10n()->t("return to the connector page").'</a>';
	} else {
		Logger::log("pumpio_connect: could not connect");
		$o = 'Could not connect to pumpio. Refresh the page or try again later.';
	}

	return $o;
}

function pumpio_jot_nets(App $a, array &$jotnets_fields)
{
	if (! local_user()) {
		return;
	}

	if (DI::pConfig()->get(local_user(), 'pumpio', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'pumpio_enable',
				DI::l10n()->t('Post to pumpio'),
				DI::pConfig()->get(local_user(), 'pumpio', 'post_by_default')
			]
		];
	}
}

function pumpio_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/pumpio/pumpio.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$import_enabled = DI::pConfig()->get(local_user(), 'pumpio', 'import');
	$import_checked = (($import_enabled) ? ' checked="checked" ' : '');

	$enabled = DI::pConfig()->get(local_user(), 'pumpio', 'post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = DI::pConfig()->get(local_user(), 'pumpio', 'post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$public_enabled = DI::pConfig()->get(local_user(), 'pumpio', 'public');
	$public_checked = (($public_enabled) ? ' checked="checked" ' : '');

	$mirror_enabled = DI::pConfig()->get(local_user(), 'pumpio', 'mirror');
	$mirror_checked = (($mirror_enabled) ? ' checked="checked" ' : '');

	$servername = DI::pConfig()->get(local_user(), "pumpio", "host");
	$username = DI::pConfig()->get(local_user(), "pumpio", "user");

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_pumpio_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_pumpio_expanded\'); openClose(\'settings_pumpio_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/pumpio.png" /><h3 class="connector">'. DI::l10n()->t('Pump.io Import/Export/Mirror').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_pumpio_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_pumpio_expanded\'); openClose(\'settings_pumpio_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/pumpio.png" /><h3 class="connector">'. DI::l10n()->t('Pump.io Import/Export/Mirror').'</h3>';
	$s .= '</span>';

	$s .= '<div id="pumpio-username-wrapper">';
	$s .= '<label id="pumpio-username-label" for="pumpio-username">'.DI::l10n()->t('pump.io username (without the servername)').'</label>';
	$s .= '<input id="pumpio-username" type="text" name="pumpio_user" value="'.$username.'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="pumpio-servername-wrapper">';
	$s .= '<label id="pumpio-servername-label" for="pumpio-servername">'.DI::l10n()->t('pump.io servername (without "http://" or "https://" )').'</label>';
	$s .= '<input id="pumpio-servername" type="text" name="pumpio_host" value="'.$servername.'" />';
	$s .= '</div><div class="clear"></div>';

	if (($username != '') && ($servername != '')) {
		$oauth_token = DI::pConfig()->get(local_user(), "pumpio", "oauth_token");
		$oauth_token_secret = DI::pConfig()->get(local_user(), "pumpio", "oauth_token_secret");

		$s .= '<div id="pumpio-password-wrapper">';
		if (($oauth_token == "") || ($oauth_token_secret == "")) {
			$s .= '<div id="pumpio-authenticate-wrapper">';
			$s .= '<a href="'.DI::baseUrl()->get().'/pumpio/connect">'.DI::l10n()->t("Authenticate your pump.io connection").'</a>';
			$s .= '</div><div class="clear"></div>';
		} else {
			$s .= '<div id="pumpio-import-wrapper">';
			$s .= '<label id="pumpio-import-label" for="pumpio-import">' . DI::l10n()->t('Import the remote timeline') . '</label>';
			$s .= '<input id="pumpio-import" type="checkbox" name="pumpio_import" value="1" ' . $import_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-enable-wrapper">';
			$s .= '<label id="pumpio-enable-label" for="pumpio-checkbox">' . DI::l10n()->t('Enable pump.io Post Addon') . '</label>';
			$s .= '<input id="pumpio-checkbox" type="checkbox" name="pumpio" value="1" ' . $checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-bydefault-wrapper">';
			$s .= '<label id="pumpio-bydefault-label" for="pumpio-bydefault">' . DI::l10n()->t('Post to pump.io by default') . '</label>';
			$s .= '<input id="pumpio-bydefault" type="checkbox" name="pumpio_bydefault" value="1" ' . $def_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-public-wrapper">';
			$s .= '<label id="pumpio-public-label" for="pumpio-public">' . DI::l10n()->t('Should posts be public?') . '</label>';
			$s .= '<input id="pumpio-public" type="checkbox" name="pumpio_public" value="1" ' . $public_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-mirror-wrapper">';
			$s .= '<label id="pumpio-mirror-label" for="pumpio-mirror">' . DI::l10n()->t('Mirror all public posts') . '</label>';
			$s .= '<input id="pumpio-mirror" type="checkbox" name="pumpio_mirror" value="1" ' . $mirror_checked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="pumpio-delete-wrapper">';
			$s .= '<label id="pumpio-delete-label" for="pumpio-delete">' . DI::l10n()->t('Check to delete this preset') . '</label>';
			$s .= '<input id="pumpio-delete" type="checkbox" name="pumpio_delete" value="1" />';
			$s .= '</div><div class="clear"></div>';
		}

		$s .= '</div><div class="clear"></div>';
	}

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="pumpio-submit" name="pumpio-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';
}

function pumpio_settings_post(App $a, array &$b)
{
	if (!empty($_POST['pumpio-submit'])) {
		if (!empty($_POST['pumpio_delete'])) {
			DI::pConfig()->set(local_user(), 'pumpio', 'consumer_key'      , '');
			DI::pConfig()->set(local_user(), 'pumpio', 'consumer_secret'   , '');
			DI::pConfig()->set(local_user(), 'pumpio', 'oauth_token'       , '');
			DI::pConfig()->set(local_user(), 'pumpio', 'oauth_token_secret', '');
			DI::pConfig()->set(local_user(), 'pumpio', 'post'              , false);
			DI::pConfig()->set(local_user(), 'pumpio', 'import'            , false);
			DI::pConfig()->set(local_user(), 'pumpio', 'host'              , '');
			DI::pConfig()->set(local_user(), 'pumpio', 'user'              , '');
			DI::pConfig()->set(local_user(), 'pumpio', 'public'            , false);
			DI::pConfig()->set(local_user(), 'pumpio', 'mirror'            , false);
			DI::pConfig()->set(local_user(), 'pumpio', 'post_by_default'   , false);
			DI::pConfig()->set(local_user(), 'pumpio', 'lastdate'          , 0);
			DI::pConfig()->set(local_user(), 'pumpio', 'last_id'           , '');
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

			DI::pConfig()->set(local_user(), 'pumpio', 'post'           , $_POST['pumpio'] ?? false);
			DI::pConfig()->set(local_user(), 'pumpio', 'import'         , $_POST['pumpio_import'] ?? false);
			DI::pConfig()->set(local_user(), 'pumpio', 'host'           , $host);
			DI::pConfig()->set(local_user(), 'pumpio', 'user'           , $user);
			DI::pConfig()->set(local_user(), 'pumpio', 'public'         , $_POST['pumpio_public'] ?? false);
			DI::pConfig()->set(local_user(), 'pumpio', 'mirror'         , $_POST['pumpio_mirror'] ?? false);
			DI::pConfig()->set(local_user(), 'pumpio', 'post_by_default', $_POST['pumpio_bydefault'] ?? false);

			if (!empty($_POST['pumpio_mirror'])) {
				DI::pConfig()->delete(local_user(), 'pumpio', 'lastdate');
			}
		}
	}
}

function pumpio_load_config(App $a, ConfigFileLoader $loader)
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

        if (DI::pConfig()->get($post['uid'], 'pumpio', 'import')) {
                // Don't fork if it isn't a reply to a pump.io post
                if (($post['parent'] != $post['id']) && !Post::exists(['id' => $post['parent'], 'network' => Protocol::PUMPIO])) {
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

	$pumpio_post   = intval(DI::pConfig()->get(local_user(), 'pumpio', 'post'));

	$pumpio_enable = (($pumpio_post && !empty($_REQUEST['pumpio_enable'])) ? intval($_REQUEST['pumpio_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(), 'pumpio', 'post_by_default'))) {
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
	if (!DI::pConfig()->get($b["uid"], 'pumpio', 'import') && ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))) {
		return;
	}

	Logger::log("pumpio_send: parameter ".print_r($b, true), Logger::DATA);

	if ($b['parent'] != $b['id']) {
		// Looking if its a reply to a pumpio post
		$condition = ['id' => $b['parent'], 'network' => Protocol::PUMPIO];
		$orig_post = Post::selectFirst([], $condition);

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

	if ($b['verb'] == Activity::LIKE) {
		if ($b['deleted']) {
			pumpio_action($a, $b["uid"], $b["thr-parent"], "unlike");
		} else {
			pumpio_action($a, $b["uid"], $b["thr-parent"], "like");
		}
		return;
	}

	if ($b['verb'] == Activity::DISLIKE) {
		return;
	}

	if (($b['verb'] == Activity::POST) && ($b['created'] !== $b['edited']) && !$b['deleted']) {
		pumpio_action($a, $b["uid"], $b["uri"], "update", $b["body"]);
	}

	if (($b['verb'] == Activity::POST) && $b['deleted']) {
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

	$oauth_token = DI::pConfig()->get($b['uid'], "pumpio", "oauth_token");
	$oauth_token_secret = DI::pConfig()->get($b['uid'], "pumpio", "oauth_token_secret");
	$consumer_key = DI::pConfig()->get($b['uid'], "pumpio","consumer_key");
	$consumer_secret = DI::pConfig()->get($b['uid'], "pumpio","consumer_secret");

	$host = DI::pConfig()->get($b['uid'], "pumpio", "host");
	$user = DI::pConfig()->get($b['uid'], "pumpio", "user");
	$public = DI::pConfig()->get($b['uid'], "pumpio", "public");

	if ($oauth_token && $oauth_token_secret) {
		$title = trim($b['title']);

		$content = BBCode::convert($b['body'], false, BBCode::CONNECTORS);

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

			if (($orig_post["object-type"] != "") && (strstr($orig_post["object-type"], ActivityNamespace::ACTIVITY_SCHEMA))) {
				$inReplyTo["objectType"] = str_replace(ActivityNamespace::ACTIVITY_SCHEMA, '', $orig_post["object-type"]);
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
				DI::pConfig()->set($b["uid"], "pumpio", "application_name", $user->generator->displayName);
			}

			$post_id = $user->object->id;
			Logger::log('pumpio_send '.$username.': success '.$post_id);
			if ($post_id && $iscomment) {
				Logger::log('pumpio_send '.$username.': Update extid '.$post_id." for post id ".$b['id']);
				Item::update(['extid' => $post_id], ['id' => $b['id']]);
			}
		} else {
			Logger::log('pumpio_send '.$username.': '.$url.' general error: ' . print_r($user, true));
			Worker::defer();
		}
	}
}

function pumpio_action(App $a, $uid, $uri, $action, $content = "")
{
	// Don't do likes and other stuff if you don't import the timeline
	if (!DI::pConfig()->get($uid, 'pumpio', 'import')) {
		return;
	}

	$ckey    = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, "pumpio", "user");

	$orig_post = Post::selectFirst([], ['uri' => $uri, 'uid' => $uid]);

	if (!DBA::isResult($orig_post)) {
		return;
	}

	if ($orig_post["extid"] && !strstr($orig_post["extid"], "/proxy/")) {
		$uri = $orig_post["extid"];
	} else {
		$uri = $orig_post["uri"];
	}

	if (($orig_post["object-type"] != "") && (strstr($orig_post["object-type"], ActivityNamespace::ACTIVITY_SCHEMA))) {
		$objectType = str_replace(ActivityNamespace::ACTIVITY_SCHEMA, '', $orig_post["object-type"]);
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
		Logger::log('pumpio_action '.$username.' '.$action.': general error: '.$uri);
		Worker::defer();
	}
}

function pumpio_sync(App $a)
{
	$r = q("SELECT * FROM `addon` WHERE `installed` = 1 AND `name` = 'pumpio'");

	if (!DBA::isResult($r)) {
		return;
	}

	$last = DI::config()->get('pumpio', 'last_poll');

	$poll_interval = intval(DI::config()->get('pumpio', 'poll_interval', PUMPIO_DEFAULT_POLL_INTERVAL));

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

	$abandon_days = intval(DI::config()->get('system', 'account_abandon_days'));
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
			$last_contact_check = DI::pConfig()->get($rr['uid'], 'pumpio', 'contact_check');
			if ($last_contact_check) {
				$next_contact_check = $last_contact_check + 86400;
			} else {
				$next_contact_check = 0;
			}

			if ($next_contact_check <= time()) {
				pumpio_getallusers($a, $rr["uid"]);
				DI::pConfig()->set($rr['uid'], 'pumpio', 'contact_check', time());
			}
		}
	}

	Logger::log('pumpio: cron_end');

	DI::config()->set('pumpio', 'last_poll', time());
}

function pumpio_cron(App $a, $b)
{
	Worker::add(PRIORITY_MEDIUM,"addon/pumpio/pumpio_sync.php");
}

function pumpio_fetchtimeline(App $a, $uid)
{
	$ckey    = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$lastdate = DI::pConfig()->get($uid, 'pumpio', 'lastdate');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, "pumpio", "user");

	//  get the application name for the pump.io app
	//  1st try personal config, then system config and fallback to the
	//  hostname of the node if neither one is set.
	$application_name  = DI::pConfig()->get($uid, 'pumpio', 'application_name');
	if ($application_name == "") {
		$application_name  = DI::config()->get('pumpio', 'application_name');
	}
	if ($application_name == "") {
		$application_name = DI::baseUrl()->getHostname();
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
		DI::pConfig()->set($uid, 'pumpio', 'lastdate', $lastdate);
	}
}

function pumpio_dounlike(App $a, $uid, $self, $post, $own_id)
{
	// Searching for the unliked post
	// Two queries for speed issues
	$orig_post = Post::selectFirst([], ['uri' => $post->object->id, 'uid' => $uid]);
	if (!DBA::isResult($orig_post)) {
		$orig_post = Post::selectFirst([], ['extid' => $post->object->id, 'uid' => $uid]);
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

	Item::markForDeletion(['verb' => Activity::LIKE, 'uid' => $uid, 'contact-id' => $contactid, 'thr-parent' => $orig_post['uri']]);

	if (DBA::isResult($r)) {
		Logger::log("pumpio_dounlike: unliked existing like. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
	} else {
		Logger::log("pumpio_dounlike: not found. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
	}
}

function pumpio_dolike(App $a, $uid, $self, $post, $own_id, $threadcompletion = true)
{
	if (empty($post->object->id)) {
		Logger::log('Got empty like: '.print_r($post, true), Logger::DEBUG);
		return;
	}

	// Searching for the liked post
	// Two queries for speed issues
	$orig_post = Post::selectFirst([], ['uri' => $post->object->id, 'uid' => $uid]);
	if (!DBA::isResult($orig_post)) {
		$orig_post = Post::selectFirst([], ['extid' => $post->object->id, 'uid' => $uid]);
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

	$condition = ['verb' => Activity::LIKE, 'uid' => $uid, 'contact-id' => $contactid, 'thr-parent' => $orig_post['uri']];
	if (Post::exists($condition)) {
		Logger::log("pumpio_dolike: found existing like. User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
		return;
	}

	$likedata = [];
	$likedata['parent'] = $orig_post['id'];
	$likedata['verb'] = Activity::LIKE;
	$likedata['gravity'] = GRAVITY_ACTIVITY;
	$likedata['uid'] = $uid;
	$likedata['wall'] = 0;
	$likedata['network'] = Protocol::PUMPIO;
	$likedata['uri'] = Item::newURI($uid);
	$likedata['thr-parent'] = $orig_post['uri'];
	$likedata['contact-id'] = $contactid;
	$likedata['app'] = $post->generator->displayName;
	$likedata['author-name'] = $post->actor->displayName;
	$likedata['author-link'] = $post->actor->url;
	if (!empty($post->actor->image)) {
		$likedata['author-avatar'] = $post->actor->image->url;
	}

	$author  = '[url=' . $likedata['author-link'] . ']' . $likedata['author-name'] . '[/url]';
	$objauthor =  '[url=' . $orig_post['author-link'] . ']' . $orig_post['author-name'] . '[/url]';
	$post_type = DI::l10n()->t('status');
	$plink = '[url=' . $orig_post['plink'] . ']' . $post_type . '[/url]';
	$likedata['object-type'] = Activity\ObjectType::NOTE;

	$likedata['body'] = DI::l10n()->t('%1$s likes %2$s\'s %3$s', $author, $objauthor, $plink);

	$likedata['object'] = '<object><type>' . Activity\ObjectType::NOTE . '</type><local>1</local>' .
		'<id>' . $orig_post['uri'] . '</id><link>' . XML::escape('<link rel="alternate" type="text/html" href="' . XML::escape($orig_post['plink']) . '" />') . '</link><title>' . $orig_post['title'] . '</title><content>' . $orig_post['body'] . '</content></object>';

	$ret = Item::insert($likedata);

	Logger::log("pumpio_dolike: ".$ret." User ".$own_id." ".$uid." Contact: ".$contactid." Url ".$orig_post['uri']);
}

function pumpio_get_contact($uid, $contact, $no_insert = false)
{
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
		Contact::updateAvatar($contact_id, $contact->image->url);
	}

	return $contact_id;
}

function pumpio_dodelete(App $a, $uid, $self, $post, $own_id)
{
	// Two queries for speed issues
	$condition = ['uri' => $post->object->id, 'uid' => $uid];
	if (Post::exists($condition)) {
		Item::markForDeletion($condition);
		return true;
	}

	$condition = ['extid' => $post->object->id, 'uid' => $uid];
	if (Post::exists($condition)) {
		Item::markForDeletion($condition);
		return true;
	}
	return false;
}

function pumpio_dopost(App $a, $client, $uid, $self, $post, $own_id, $threadcompletion = true)
{
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
		if (Post::exists(['uri' => $post->object->id, 'uid' => $uid])) {
			return false;
		}
		if (Post::exists(['extid' => $post->object->id, 'uid' => $uid])) {
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
	$postarray['object-type'] = ActivityNamespace::ACTIVITY_SCHEMA . strtolower($post->object->objectType);

	if ($post->object->objectType != "comment") {
		$contact_id = pumpio_get_contact($uid, $post->actor);

		if (!$contact_id) {
			$contact_id = $self[0]['id'];
		}

		$postarray['thr-parent'] = $post->object->id;

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

		$postarray['thr-parent'] = $post->object->inReplyTo->id;
	}

	// When there is no content there is no need to continue
	if (empty($post->object->content)) {
		return false;
	}

	if (!empty($post->object->pump_io->proxyURL)) {
		$postarray['extid'] = $post->object->pump_io->proxyURL;
	}

	$postarray['contact-id'] = $contact_id;
	$postarray['verb'] = Activity::POST;
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

		$postarray['body'] = Friendica\Content\Text\BBCode::getShareOpeningTag($share_author, $post->object->author->url,
						$post->object->author->image->url, $post->links->self->href, $created) .
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
		pumpio_fetchallcomments($a, $uid, $postarray['thr-parent']);
	}

	return $top_item;
}

function pumpio_fetchinbox(App $a, $uid)
{
	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$lastdate = DI::pConfig()->get($uid, 'pumpio', 'lastdate');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, "pumpio", "user");

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

	$last_id = DI::pConfig()->get($uid, 'pumpio', 'last_id');

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

	DI::pConfig()->set($uid, 'pumpio', 'last_id', $last_id);
}

function pumpio_getallusers(App &$a, $uid)
{
	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, "pumpio", "user");

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

function pumpio_getreceiver(App $a, array $b)
{
	$receiver = [];

	if (!$b["private"]) {
		if (!strstr($b['postopts'], 'pumpio')) {
			return $receiver;
		}

		$public = DI::pConfig()->get($b['uid'], "pumpio", "public");

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
	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, "pumpio", "user");

	Logger::log("pumpio_fetchallcomments: completing comment for user ".$uid." post id ".$id);

	$own_id = "https://".$hostname."/".$username;

	$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	// Fetching the original post
	$condition = ["`uri` = ? AND `uid` = ? AND `extid` != ''", $id, $uid];
	$original = Post::selectFirst(['extid'], $condition);
	if (!DBA::isResult($original)) {
		return false;
	}

	$url = $original["extid"];

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
		if (Post::exists(['uri' => $item->id, 'uid' => $uid])) {
			continue;
		}

		if (Post::exists(['extid' => $item->id, 'uid' => $uid])) {
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
	return DI::httpRequest()->get($url, ['timeout' => 10])->isSuccess();
}

/*
To-Do:
 - edit own notes
 - delete own notes
*/
