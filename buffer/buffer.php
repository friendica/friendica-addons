<?php
/**
 * Name: Buffer Post Connector
 * Description: Post to Buffer (Facebook Pages, LinkedIn, Twitter)
 * Version: 0.2
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 * Status: Unsupported
 */
require 'addon/buffer/bufferapp.php';

use Friendica\App;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Util\Proxy as ProxyUtils;
use Friendica\Util\Strings;

function buffer_install()
{
	Hook::register('hook_fork',            'addon/buffer/buffer.php', 'buffer_hook_fork');
	Hook::register('post_local',           'addon/buffer/buffer.php', 'buffer_post_local');
	Hook::register('notifier_normal',      'addon/buffer/buffer.php', 'buffer_send');
	Hook::register('jot_networks',         'addon/buffer/buffer.php', 'buffer_jot_nets');
	Hook::register('connector_settings',      'addon/buffer/buffer.php', 'buffer_settings');
	Hook::register('connector_settings_post', 'addon/buffer/buffer.php', 'buffer_settings_post');
}

function buffer_module()
{
}

function buffer_content(App $a)
{
	if (! local_user()) {
		notice(DI::l10n()->t('Permission denied.') . EOL);
		return '';
	}

	require_once "mod/settings.php";
	settings_init($a);

	if (isset(DI::args()->getArgv()[1])) {
		switch (DI::args()->getArgv()[1]) {
			case "connect":
				$o = buffer_connect($a);
				break;

			default:
				$o = print_r(DI::args()->getArgv(), true);
				break;
		}
	} else {
		$o = buffer_connect($a);
	}

	return $o;
}

function buffer_addon_admin(App $a, &$o)
{
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/buffer/");

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		// name, label, value, help, [extra values]
		'$client_id' => ['client_id', DI::l10n()->t('Client ID'), DI::config()->get('buffer', 'client_id'), ''],
		'$client_secret' => ['client_secret', DI::l10n()->t('Client Secret'), DI::config()->get('buffer', 'client_secret'), ''],
	]);
}

function buffer_addon_admin_post(App $a)
{
	$client_id     = (!empty($_POST['client_id'])     ? Strings::escapeTags(trim($_POST['client_id']))     : '');
	$client_secret = (!empty($_POST['client_secret']) ? Strings::escapeTags(trim($_POST['client_secret'])) : '');

	DI::config()->set('buffer', 'client_id'    , $client_id);
	DI::config()->set('buffer', 'client_secret', $client_secret);
}

function buffer_connect(App $a)
{
	if (isset($_REQUEST["error"])) {
		$o = DI::l10n()->t('Error when registering buffer connection:')." ".$_REQUEST["error"];
		return $o;
	}

	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Define the needed keys
	$client_id = DI::config()->get('buffer','client_id');
	$client_secret = DI::config()->get('buffer','client_secret');

	// The callback URL is the script that gets called after the user authenticates with buffer
	$callback_url = DI::baseUrl()->get()."/buffer/connect";

	$buffer = new BufferApp($client_id, $client_secret, $callback_url);

	if (!$buffer->ok) {
		$o = '<a href="' . $buffer->get_login_url() . '">Connect to Buffer!</a>';
	} else {
		Logger::notice("buffer_connect: authenticated");
		$o = DI::l10n()->t("You are now authenticated to buffer. ");
		$o .= '<br /><a href="' . DI::baseUrl()->get() . '/settings/connectors">' . DI::l10n()->t("return to the connector page") . '</a>';
		DI::pConfig()->set(local_user(), 'buffer','access_token', $buffer->access_token);
	}

	return $o;
}

function buffer_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (DI::pConfig()->get(local_user(), 'buffer', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'buffer_enable',
				DI::l10n()->t('Post to Buffer'),
				DI::pConfig()->get(local_user(), 'buffer', 'post_by_default')
			]
		];
	}
}

function buffer_settings(App $a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/buffer/buffer.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = DI::pConfig()->get(local_user(),'buffer','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = DI::pConfig()->get(local_user(),'buffer','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_buffer_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_buffer_expanded\'); openClose(\'settings_buffer_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/buffer.png" /><h3 class="connector">'. DI::l10n()->t('Buffer Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_buffer_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_buffer_expanded\'); openClose(\'settings_buffer_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/buffer.png" /><h3 class="connector">'. DI::l10n()->t('Buffer Export').'</h3>';
	$s .= '</span>';

	$client_id = DI::config()->get("buffer", "client_id");
	$client_secret = DI::config()->get("buffer", "client_secret");
	$access_token = DI::pConfig()->get(local_user(), "buffer", "access_token");

	$s .= '<div id="buffer-password-wrapper">';

	if ($access_token == "") {
		$s .= '<div id="buffer-authenticate-wrapper">';
		$s .= '<a href="'.DI::baseUrl()->get().'/buffer/connect">'.DI::l10n()->t("Authenticate your Buffer connection").'</a>';
		$s .= '</div><div class="clear"></div>';
	} else {
		$s .= '<div id="buffer-enable-wrapper">';
		$s .= '<label id="buffer-enable-label" for="buffer-checkbox">' . DI::l10n()->t('Enable Buffer Post Addon') . '</label>';
		$s .= '<input id="buffer-checkbox" type="checkbox" name="buffer" value="1" ' . $checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="buffer-bydefault-wrapper">';
		$s .= '<label id="buffer-bydefault-label" for="buffer-bydefault">' . DI::l10n()->t('Post to Buffer by default') . '</label>';
		$s .= '<input id="buffer-bydefault" type="checkbox" name="buffer_bydefault" value="1" ' . $def_checked . '/>';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="buffer-delete-wrapper">';
		$s .= '<label id="buffer-delete-label" for="buffer-delete">' . DI::l10n()->t('Check to delete this preset') . '</label>';
		$s .= '<input id="buffer-delete" type="checkbox" name="buffer_delete" value="1" />';
		$s .= '</div><div class="clear"></div>';

		// The callback URL is the script that gets called after the user authenticates with buffer
		$callback_url = DI::baseUrl()->get() . '/buffer/connect';

		$buffer = new BufferApp($client_id, $client_secret, $callback_url, $access_token);

		$profiles = $buffer->go('/profiles');
		if (is_array($profiles)) {
			$s .= '<div id="buffer-accounts-wrapper">';
			$s .= DI::l10n()->t("Posts are going to all accounts that are enabled by default:");
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

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="buffer-submit" name="buffer-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';
}


function buffer_settings_post(App $a, array &$b)
{
	if (!empty($_POST['buffer-submit'])) {
		if (!empty($_POST['buffer_delete'])) {
			DI::pConfig()->set(local_user(), 'buffer', 'access_token'   , '');
			DI::pConfig()->set(local_user(), 'buffer', 'post'           , false);
			DI::pConfig()->set(local_user(), 'buffer', 'post_by_default', false);
		} else {
			DI::pConfig()->set(local_user(), 'buffer', 'post'           , intval($_POST['buffer'] ?? false));
			DI::pConfig()->set(local_user(), 'buffer', 'post_by_default', intval($_POST['buffer_bydefault'] ?? false));
		}
	}
}

function buffer_post_local(App $a, array &$b)
{
	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	$buffer_post   = intval(DI::pConfig()->get(local_user(),'buffer','post'));

	$buffer_enable = (($buffer_post && !empty($_REQUEST['buffer_enable'])) ? intval($_REQUEST['buffer_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(),'buffer','post_by_default'))) {
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

function buffer_hook_fork(&$a, &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'buffer') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
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

	$client_id = DI::config()->get("buffer", "client_id");
	$client_secret = DI::config()->get("buffer", "client_secret");
	$access_token = DI::pConfig()->get($b['uid'], "buffer","access_token");
	$callback_url = "";

	if ($access_token) {
		$buffer = new BufferApp($client_id, $client_secret, $callback_url, $access_token);

		$profiles = $buffer->go('/profiles');
		if (is_array($profiles)) {
			Logger::info("Will send these parameter ".print_r($b, true));

			foreach ($profiles as $profile) {
				if (!$profile->default)
					continue;

				$send = false;

				switch ($profile->service) {
					case 'facebook':
						$send = ($b["extid"] != Protocol::FACEBOOK);
						$limit = 0;
						$includedlinks = false;
						$htmlmode = 9;
						break;

					case 'twitter':
						$send = ($b["extid"] != Protocol::TWITTER);
						$limit = 280;
						$includedlinks = true;
						$htmlmode = 8;
						break;

					case 'linkedin':
						$send = ($b["extid"] != Protocol::LINKEDIN);
						$limit = 700;
						$includedlinks = true;
						$htmlmode = 2;
						break;
				}

				if (!$send)
					continue;

				$item = $b;

				$post = Plaintext::getPost($item, $limit, $includedlinks, $htmlmode);
				Logger::info("buffer_send: converted message ".$b["id"]." result: ".print_r($post, true));

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
				Logger::info("buffer_send: data for message " . $b["id"] . ": " . print_r($message, true));
				$ret = $buffer->go('/updates/create', $message);
				Logger::info("buffer_send: send message " . $b["id"] . " result: " . print_r($ret, true));
			}
		}
	}
}
