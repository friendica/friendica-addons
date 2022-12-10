<?php

/**
 * Name: GNU Social Connector
 * Description: Bidirectional (posting, relaying and reading) connector for GNU Social.
 * Version: 1.0.5
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 * Copyright (c) 2011-2013 Tobias Diekershoff, Michael Vogel
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above
 *    * copyright notice, this list of conditions and the following disclaimer in
 *      the documentation and/or other materials provided with the distribution.
 *    * Neither the name of the <organization> nor the names of its contributors
 *      may be used to endorse or promote products derived from this software
 *      without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */
define('STATUSNET_DEFAULT_POLL_INTERVAL', 5); // given in minutes

require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'statusnetoauth.php';

use CodebirdSN\CodebirdSN;
use Friendica\App;
use Friendica\Content\OEmbed;
use Friendica\Content\PageInfo;
use Friendica\Content\Text\HTML;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\Photo;
use Friendica\Model\Post;
use Friendica\Model\User;
use Friendica\Network\HTTPClient\Client\HttpClientAccept;
use Friendica\Protocol\Activity;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Strings;
use GuzzleHttp\Exception\TransferException;

function statusnet_install()
{
	//  we need some hooks, for the configuration and for sending tweets
	Hook::register('connector_settings', 'addon/statusnet/statusnet.php', 'statusnet_settings');
	Hook::register('connector_settings_post', 'addon/statusnet/statusnet.php', 'statusnet_settings_post');
	Hook::register('notifier_normal', 'addon/statusnet/statusnet.php', 'statusnet_post_hook');
	Hook::register('hook_fork', 'addon/statusnet/statusnet.php', 'statusnet_hook_fork');
	Hook::register('post_local', 'addon/statusnet/statusnet.php', 'statusnet_post_local');
	Hook::register('jot_networks', 'addon/statusnet/statusnet.php', 'statusnet_jot_nets');
	Hook::register('cron', 'addon/statusnet/statusnet.php', 'statusnet_cron');
	Hook::register('prepare_body', 'addon/statusnet/statusnet.php', 'statusnet_prepare_body');
	Hook::register('check_item_notification', 'addon/statusnet/statusnet.php', 'statusnet_check_item_notification');
	Logger::notice('installed GNU Social');
}

function statusnet_check_item_notification(App $a, &$notification_data)
{
	if (DI::pConfig()->get($notification_data['uid'], 'statusnet', 'post')) {
		$notification_data['profiles'][] = DI::pConfig()->get($notification_data['uid'], 'statusnet', 'own_url');
	}
}

function statusnet_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (DI::pConfig()->get(local_user(), 'statusnet', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'statusnet_enable',
				DI::l10n()->t('Post to GNU Social'),
				DI::pConfig()->get(local_user(), 'statusnet', 'post_by_default')
			]
		];
	}
}

function statusnet_settings_post(App $a, $post)
{
	if (!local_user()) {
		return;
	}
	// don't check GNU Social settings if GNU Social submit button is not clicked
	if (empty($_POST['statusnet-submit']) && empty($_POST['statusnet-disconnect'])) {
		return;
	}

	if (!empty($_POST['statusnet-disconnect'])) {
		/*		 * *
		 * if the GNU Social-disconnect button is clicked, clear the GNU Social configuration
		 */
		DI::pConfig()->delete(local_user(), 'statusnet', 'consumerkey');
		DI::pConfig()->delete(local_user(), 'statusnet', 'consumersecret');
		DI::pConfig()->delete(local_user(), 'statusnet', 'post');
		DI::pConfig()->delete(local_user(), 'statusnet', 'post_by_default');
		DI::pConfig()->delete(local_user(), 'statusnet', 'oauthtoken');
		DI::pConfig()->delete(local_user(), 'statusnet', 'oauthsecret');
		DI::pConfig()->delete(local_user(), 'statusnet', 'baseapi');
		DI::pConfig()->delete(local_user(), 'statusnet', 'lastid');
		DI::pConfig()->delete(local_user(), 'statusnet', 'mirror_posts');
		DI::pConfig()->delete(local_user(), 'statusnet', 'import');
		DI::pConfig()->delete(local_user(), 'statusnet', 'create_user');
		DI::pConfig()->delete(local_user(), 'statusnet', 'own_url');
	} else {
		if (isset($_POST['statusnet-preconf-apiurl'])) {
			/*			 * *
			 * If the user used one of the preconfigured GNU Social server credentials
			 * use them. All the data are available in the global config.
			 * Check the API Url never the less and blame the admin if it's not working ^^
			 */
			$globalsn = DI::config()->get('statusnet', 'sites');
			foreach ($globalsn as $asn) {
				if ($asn['apiurl'] == $_POST['statusnet-preconf-apiurl']) {
					$apibase = $asn['apiurl'];
					$c = DI::httpClient()->fetch($apibase . 'statusnet/version.xml');
					if (strlen($c) > 0) {
						DI::pConfig()->set(local_user(), 'statusnet', 'consumerkey', $asn['consumerkey']);
						DI::pConfig()->set(local_user(), 'statusnet', 'consumersecret', $asn['consumersecret']);
						DI::pConfig()->set(local_user(), 'statusnet', 'baseapi', $asn['apiurl']);
						//DI::pConfig()->set(local_user(), 'statusnet', 'application_name', $asn['applicationname'] );
					} else {
						notice(DI::l10n()->t('Please contact your site administrator.<br />The provided API URL is not valid.') . EOL . $asn['apiurl'] . EOL);
					}
				}
			}
		} else {
			if (isset($_POST['statusnet-consumersecret'])) {
				//  check if we can reach the API of the GNU Social server
				//  we'll check the API Version for that, if we don't get one we'll try to fix the path but will
				//  resign quickly after this one try to fix the path ;-)
				$apibase = $_POST['statusnet-baseapi'];
				$c = DI::httpClient()->fetch($apibase . 'statusnet/version.xml');
				if (strlen($c) > 0) {
					//  ok the API path is correct, let's save the settings
					DI::pConfig()->set(local_user(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
					DI::pConfig()->set(local_user(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
					DI::pConfig()->set(local_user(), 'statusnet', 'baseapi', $apibase);
					//DI::pConfig()->set(local_user(), 'statusnet', 'application_name', $_POST['statusnet-applicationname'] );
				} else {
					//  the API path is not correct, maybe missing trailing / ?
					$apibase = $apibase . '/';
					$c = DI::httpClient()->fetch($apibase . 'statusnet/version.xml');
					if (strlen($c) > 0) {
						//  ok the API path is now correct, let's save the settings
						DI::pConfig()->set(local_user(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
						DI::pConfig()->set(local_user(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
						DI::pConfig()->set(local_user(), 'statusnet', 'baseapi', $apibase);
					} else {
						//  still not the correct API base, let's do noting
						notice(DI::l10n()->t('We could not contact the GNU Social API with the Path you entered.') . EOL);
					}
				}
			} else {
				if (isset($_POST['statusnet-pin'])) {
					//  if the user supplied us with a PIN from GNU Social, let the magic of OAuth happen
					$api = DI::pConfig()->get(local_user(), 'statusnet', 'baseapi');
					$ckey = DI::pConfig()->get(local_user(), 'statusnet', 'consumerkey');
					$csecret = DI::pConfig()->get(local_user(), 'statusnet', 'consumersecret');
					//  the token and secret for which the PIN was generated were hidden in the settings
					//  form as token and token2, we need a new connection to GNU Social using these token
					//  and secret to request a Access Token with the PIN
					$connection = new StatusNetOAuth($api, $ckey, $csecret, $_POST['statusnet-token'], $_POST['statusnet-token2']);
					$token = $connection->getAccessToken($_POST['statusnet-pin']);
					//  ok, now that we have the Access Token, save them in the user config
					DI::pConfig()->set(local_user(), 'statusnet', 'oauthtoken', $token['oauth_token']);
					DI::pConfig()->set(local_user(), 'statusnet', 'oauthsecret', $token['oauth_token_secret']);
					DI::pConfig()->set(local_user(), 'statusnet', 'post', 1);
					DI::pConfig()->set(local_user(), 'statusnet', 'post_taglinks', 1);
					//  reload the Addon Settings page, if we don't do it see Bug #42
				} else {
					//  if no PIN is supplied in the POST variables, the user has changed the setting
					//  to post a dent for every new __public__ posting to the wall
					DI::pConfig()->set(local_user(), 'statusnet', 'post', intval($_POST['statusnet-enable']));
					DI::pConfig()->set(local_user(), 'statusnet', 'post_by_default', intval($_POST['statusnet-default']));
					DI::pConfig()->set(local_user(), 'statusnet', 'mirror_posts', intval($_POST['statusnet-mirror']));
					DI::pConfig()->set(local_user(), 'statusnet', 'import', intval($_POST['statusnet-import']));
					DI::pConfig()->set(local_user(), 'statusnet', 'create_user', intval($_POST['statusnet-create_user']));

					if (!intval($_POST['statusnet-mirror']))
						DI::pConfig()->delete(local_user(), 'statusnet', 'lastid');
				}
			}
		}
	}
}

function statusnet_settings(App $a, array &$data)
{
	if (!local_user()) {
		return;
	}

	DI::page()->registerStylesheet(__DIR__ . '/statusnet.css', 'all');

	/*	 * *
	 * 1) Check that we have a base api url and a consumer key & secret
	 * 2) If no OAuthtoken & stuff is present, generate button to get some
	 *    allow the user to cancel the connection process at this step
	 * 3) Checkbox for "Send public notices (respect size limitation)
	 */
	$baseapi            = DI::pConfig()->get(local_user(), 'statusnet', 'baseapi');
	$ckey               = DI::pConfig()->get(local_user(), 'statusnet', 'consumerkey');
	$csecret            = DI::pConfig()->get(local_user(), 'statusnet', 'consumersecret');
	$otoken             = DI::pConfig()->get(local_user(), 'statusnet', 'oauthtoken');
	$osecret            = DI::pConfig()->get(local_user(), 'statusnet', 'oauthsecret');
	$enabled            = DI::pConfig()->get(local_user(), 'statusnet', 'post', false);
	$def_enabled        = DI::pConfig()->get(local_user(), 'statusnet', 'post_by_default', false);
	$mirror_enabled     = DI::pConfig()->get(local_user(), 'statusnet', 'mirror_posts', false);
	$createuser_enabled = DI::pConfig()->get(local_user(), 'statusnet', 'create_user', false);
	$import             = DI::pConfig()->get(local_user(), 'statusnet', 'import');

	// Radio button list to select existing application credentials
	$sites = array_map(function ($site) {
		return ['statusnet-preconf-apiurl', $site['sitename'], $site['apiurl']];
	}, DI::config()->get('statusnet', 'sites', []));

	$submit = ['statusnet-submit' => DI::l10n()->t('Save Settings')];

	if ($ckey && $csecret) {
		if ($otoken && $osecret) {
			/*			 * *
			 *  we have an OAuth key / secret pair for the user
			 *  so let's give a chance to disable the postings to GNU Social
			 */
			$connection = new StatusNetOAuth($baseapi, $ckey, $csecret, $otoken, $osecret);
			$account    = $connection->get('account/verify_credentials');

			if (!empty($account)) {
				$connected_account = DI::l10n()->t('Currently connected to: <a href="%s" target="_statusnet">%s</a>', $account->statusnet_profile_url, $account->screen_name);
			}

			$user = User::getById(local_user());
			if ($user['hidewall']) {
				$privacy_warning = DI::l10n()->t('<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.');
			}

			$submit['statusnet-disconnect'] = DI::l10n()->t('Clear OAuth configuration');
		} else {
			/*			 * *
			 * the user has not yet connected the account to GNU Social
			 * get a temporary OAuth key/secret pair and display a button with
			 * which the user can request a PIN to connect the account to a
			 * account at GNU Social
			 */
			$connection    = new StatusNetOAuth($baseapi, $ckey, $csecret);
			$request_token = $connection->getRequestToken('oob');
			$authorize_url = $connection->getAuthorizeURL($request_token['oauth_token'], false);

			$submit['statusnet-disconnect'] = DI::l10n()->t('Cancel GNU Social Connection');
		}
	}


	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/statusnet/');
	$html = Renderer::replaceMacros($t, [
		'$l10n' => [
			'global_title'      => DI::l10n()->t('Globally Available GNU Social OAuthKeys'),
			'global_info'       => DI::l10n()->t(DI::l10n()->t('There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below).')),
			'credentials_title' => DI::l10n()->t('Provide your own OAuth Credentials'),
			'credentials_info'  => DI::l10n()->t('No consumer key pair for GNU Social found. Register your Friendica Account as a desktop application on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorite GNU Social installation.'),
			'oauth_info'        => DI::l10n()->t('To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.'),
			'oauth_alt'         => DI::l10n()->t('Log in with GNU Social'),
			'oauth_cancel'      => DI::l10n()->t('Cancel Connection Process'),
			'oauth_api'         => DI::l10n()->t('Current GNU Social API is: %s', $baseapi),
			'connected_account' => $connected_account ?? '',
			'privacy_warning'   => $privacy_warning ?? '',
		],

		'$ckey'    => $ckey,
		'$csecret' => $csecret,
		'$otoken'  => $otoken,
		'$osecret' => $osecret,
		'$sites'   => $sites,

		'$authorize_url' => $authorize_url ?? '',
		'$request_token' => $request_token ?? null,
		'$account'       => $account ?? null,

		'$authenticate_url' => DI::baseUrl()->get() . '/statusnet/connect',

		'$consumerkey'    => ['statusnet-consumerkey', DI::l10n()->t('OAuth Consumer Key'), '', '', false, ' size="35'],
		'$consumersecret' => ['statusnet-consumersecret', DI::l10n()->t('OAuth Consumer Secret'), '', '', false, ' size="35'],

		'$baseapi' => ['statusnet-baseapi', DI::l10n()->t('Base API Path (remember the trailing /)'), '', '', false, ' size="35'],
		'$pin'     => ['statusnet-pin', DI::l10n()->t('Copy the security code from GNU Social here')],

		'$enable'      => ['statusnet-enabled', DI::l10n()->t('Allow posting to GNU Social'), $enabled, DI::l10n()->t('If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.')],
		'$default'     => ['statusnet-default', DI::l10n()->t('Post to GNU Social by default'), $def_enabled],
		'$mirror'      => ['statusnet-mirror', DI::l10n()->t('Mirror all public posts'), $mirror_enabled],
		'$create_user' => ['statusnet-create_user', DI::l10n()->t('Automatically create contacts'), $createuser_enabled],
		'$import'      => ['statusnet-import', DI::l10n()->t('Import the remote timeline'), $import, '', [
			0 => DI::l10n()->t('Disabled'),
			1 => DI::l10n()->t('Full Timeline'),
			2 => DI::l10n()->t('Only Mentions'),
		]],
	]);

	$data = [
		'connector' => 'statusnet',
		'title'     => DI::l10n()->t('GNU Social Import/Export/Mirror'),
		'image'     => 'images/gnusocial.png',
		'enabled'   => $enabled,
		'html'      => $html,
		'submit'    => $submit,
	];
}

function statusnet_hook_fork(App $a, array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	// Deleting and editing is not supported by the addon
	if ($post['deleted'] || ($post['created'] !== $post['edited'])) {
		$b['execute'] = false;
		return;
	}

	// if post comes from GNU Social don't send it back
	if ($post['extid'] == Protocol::STATUSNET) {
		$b['execute'] = false;
		return;
	}

	if ($post['app'] == 'StatusNet') {
		$b['execute'] = false;
		return;
	}

	if (DI::pConfig()->get($post['uid'], 'statusnet', 'import')) {
		// Don't fork if it isn't a reply to a GNU Social post
		if (($post['parent'] != $post['id']) && !Post::exists(['id' => $post['parent'], 'network' => Protocol::STATUSNET])) {
			Logger::notice('No GNU Social parent found for item ' . $post['id']);
			$b['execute'] = false;
			return;
		}
	} else {
		// Comments are never exported when we don't import the GNU Social timeline
		if (!strstr($post['postopts'], 'statusnet') || ($post['parent'] != $post['id']) || $post['private']) {
			$b['execute'] = false;
			return;
		}
	}
}

function statusnet_post_local(App $a, &$b)
{
	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	$statusnet_post = DI::pConfig()->get(local_user(), 'statusnet', 'post');
	$statusnet_enable = (($statusnet_post && !empty($_REQUEST['statusnet_enable'])) ? intval($_REQUEST['statusnet_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(), 'statusnet', 'post_by_default'))) {
		$statusnet_enable = 1;
	}

	if (!$statusnet_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'statusnet';
}

function statusnet_action(App $a, $uid, $pid, $action)
{
	$api = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$ckey = DI::pConfig()->get($uid, 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($uid, 'statusnet', 'consumersecret');
	$otoken = DI::pConfig()->get($uid, 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'statusnet', 'oauthsecret');

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	Logger::debug('statusnet_action "' . $action . '" ID: ' . $pid);

	switch ($action) {
		case 'delete':
			$result = $connection->post('statuses/destroy/' . $pid);
			break;

		case 'like':
			$result = $connection->post('favorites/create/' . $pid);
			break;

		case 'unlike':
			$result = $connection->post('favorites/destroy/' . $pid);
			break;
	}
	Logger::info('statusnet_action "' . $action . '" send, result: ' . print_r($result, true));
}

function statusnet_post_hook(App $a, &$b)
{
	/**
	 * Post to GNU Social
	 */
	if (!DI::pConfig()->get($b['uid'], 'statusnet', 'import')) {
		if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
			return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], $b['body']);

	$api = DI::pConfig()->get($b['uid'], 'statusnet', 'baseapi');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	if ($b['parent'] != $b['id']) {
		Logger::debug('statusnet_post_hook: parameter ', ['b' => $b]);

		// Looking if its a reply to a GNU Social post
		$hostlength = strlen($hostname) + 2;
		if ((substr($b['parent-uri'], 0, $hostlength) != $hostname . '::') && (substr($b['extid'], 0, $hostlength) != $hostname . '::') && (substr($b['thr-parent'], 0, $hostlength) != $hostname . '::')) {
			Logger::notice('statusnet_post_hook: no GNU Social post ' . $b['parent']);
			return;
		}

		$condition = ['uri' => $b['thr-parent'], 'uid' => $b['uid']];
		$orig_post = Post::selectFirst(['author-link', 'uri'], $condition);
		if (!DBA::isResult($orig_post)) {
			Logger::notice('statusnet_post_hook: no parent found ' . $b['thr-parent']);
			return;
		} else {
			$iscomment = true;
		}

		$nick = preg_replace("=https?://(.*)/(.*)=ism", "$2", $orig_post['author-link']);

		$nickname = '@[url=' . $orig_post['author-link'] . ']' . $nick . '[/url]';
		$nicknameplain = '@' . $nick;

		Logger::info('statusnet_post_hook: comparing ' . $nickname . ' and ' . $nicknameplain . ' with ' . $b['body']);
		if ((strpos($b['body'], $nickname) === false) && (strpos($b['body'], $nicknameplain) === false)) {
			$b['body'] = $nickname . ' ' . $b['body'];
		}

		Logger::info('statusnet_post_hook: parent found ', ['orig_post' => $orig_post]);
	} else {
		$iscomment = false;

		if ($b['private'] || !strstr($b['postopts'], 'statusnet')) {
			return;
		}

		// Dont't post if the post doesn't belong to us.
		// This is a check for forum postings
		$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
		if ($b['contact-id'] != $self['id']) {
			return;
		}
	}

	if (($b['verb'] == Activity::POST) && $b['deleted']) {
		statusnet_action($a, $b['uid'], substr($orig_post['uri'], $hostlength), 'delete');
	}

	if ($b['verb'] == Activity::LIKE) {
		Logger::info('statusnet_post_hook: parameter 2 ' . substr($b['thr-parent'], $hostlength));
		if ($b['deleted'])
			statusnet_action($a, $b['uid'], substr($b['thr-parent'], $hostlength), 'unlike');
		else
			statusnet_action($a, $b['uid'], substr($b['thr-parent'], $hostlength), 'like');
		return;
	}

	if ($b['deleted'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	// if posts comes from GNU Social don't send it back
	if ($b['extid'] == Protocol::STATUSNET) {
		return;
	}

	if ($b['app'] == 'StatusNet') {
		return;
	}

	Logger::notice('GNU Socialpost invoked');

	DI::pConfig()->load($b['uid'], 'statusnet');

	$api     = DI::pConfig()->get($b['uid'], 'statusnet', 'baseapi');
	$ckey    = DI::pConfig()->get($b['uid'], 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($b['uid'], 'statusnet', 'consumersecret');
	$otoken  = DI::pConfig()->get($b['uid'], 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($b['uid'], 'statusnet', 'oauthsecret');

	if ($ckey && $csecret && $otoken && $osecret) {
		// If it's a repeated message from GNU Social then do a native retweet and exit
		if (statusnet_is_retweet($a, $b['uid'], $b['body'])) {
			return;
		}

		$dent = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);
		$max_char = $dent->get_maxlength(); // max. length for a dent

		DI::pConfig()->set($b['uid'], 'statusnet', 'max_char', $max_char);

		$tempfile = '';
		$msgarr = Plaintext::getPost($b, $max_char, true, 7);
		$msg = $msgarr['text'];

		if (($msg == '') && isset($msgarr['title']))
			$msg = Plaintext::shorten($msgarr['title'], $max_char - 50, $b['uid']);

		$image = '';

		if (isset($msgarr['url']) && ($msgarr['type'] != 'photo')) {
			$msg .= " \n" . $msgarr['url'];
		} elseif (isset($msgarr['image']) && ($msgarr['type'] != 'video')) {
			$image = $msgarr['image'];
		}

		if ($image != '') {
			$img_str = DI::httpClient()->fetch($image);
			$tempfile = tempnam(System::getTempPath(), 'cache');
			file_put_contents($tempfile, $img_str);
			$postdata = ['status' => $msg, 'media[]' => $tempfile];
		} else {
			$postdata = ['status' => $msg];
		}

		// and now send it :-)
		if (strlen($msg)) {
			if ($iscomment) {
				$postdata['in_reply_to_status_id'] = substr($orig_post['uri'], $hostlength);
				Logger::info('statusnet_post send reply ' . print_r($postdata, true));
			}

			// New code that is able to post pictures
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'codebirdsn.php';
			$cb = CodebirdSN::getInstance();
			$cb->setAPIEndpoint($api);
			$cb->setConsumerKey($ckey, $csecret);
			$cb->setToken($otoken, $osecret);
			$result = $cb->statuses_update($postdata);
			//$result = $dent->post('statuses/update', $postdata);
			Logger::info('statusnet_post send, result: ' . print_r($result, true) .
				"\nmessage: " . $msg . "\nOriginal post: " . print_r($b, true) . "\nPost Data: " . print_r($postdata, true));

			if (!empty($result->source)) {
				DI::pConfig()->set($b['uid'], 'statusnet', 'application_name', strip_tags($result->source));
			}

			if (!empty($result->error)) {
				Logger::notice('Send to GNU Social failed: "' . $result->error . '"');
			} elseif ($iscomment) {
				Logger::notice('statusnet_post: Update extid ' . $result->id . ' for post id ' . $b['id']);
				Item::update(['extid' => $hostname . '::' . $result->id, 'body' => $result->text], ['id' => $b['id']]);
			}
		}
		if ($tempfile != '') {
			unlink($tempfile);
		}
	}
}

function statusnet_addon_admin_post(App $a)
{
	$sites = [];

	foreach ($_POST['sitename'] as $id => $sitename) {
		$sitename = trim($sitename);
		$apiurl = trim($_POST['apiurl'][$id]);
		if (!(substr($apiurl, -1) == '/')) {
			$apiurl = $apiurl . '/';
		}
		$secret = trim($_POST['secret'][$id]);
		$key = trim($_POST['key'][$id]);
		//$applicationname = (!empty($_POST['applicationname']) ? Strings::escapeTags(trim($_POST['applicationname'][$id])):'');
		if ($sitename != '' &&
			$apiurl != '' &&
			$secret != '' &&
			$key != '' &&
			empty($_POST['delete'][$id])) {

			$sites[] = [
				'sitename' => $sitename,
				'apiurl' => $apiurl,
				'consumersecret' => $secret,
				'consumerkey' => $key,
				//'applicationname' => $applicationname
			];
		}
	}

	$sites = DI::config()->set('statusnet', 'sites', $sites);
}

function statusnet_addon_admin(App $a, &$o)
{
	$sites = DI::config()->get('statusnet', 'sites');
	$sitesform = [];
	if (is_array($sites)) {
		foreach ($sites as $id => $s) {
			$sitesform[] = [
				'sitename' => ["sitename[$id]", "Site name", $s['sitename'], ""],
				'apiurl' => ["apiurl[$id]", "Api url", $s['apiurl'], DI::l10n()->t("Base API Path \x28remember the trailing /\x29")],
				'secret' => ["secret[$id]", "Secret", $s['consumersecret'], ""],
				'key' => ["key[$id]", "Key", $s['consumerkey'], ""],
				//'applicationname' => Array("applicationname[$id]", "Application name", $s['applicationname'], ""),
				'delete' => ["delete[$id]", "Delete", False, "Check to delete this preset"],
			];
		}
	}
	/* empty form to add new site */
	$id = count($sitesform);
	$sitesform[] = [
		'sitename' => ["sitename[$id]", DI::l10n()->t("Site name"), "", ""],
		'apiurl' => ["apiurl[$id]", "Api url", "", DI::l10n()->t("Base API Path \x28remember the trailing /\x29")],
		'secret' => ["secret[$id]", DI::l10n()->t("Consumer Secret"), "", ""],
		'key' => ["key[$id]", DI::l10n()->t("Consumer Key"), "", ""],
		//'applicationname' => Array("applicationname[$id]", DI::l10n()->t("Application name"), "", ""),
	];

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/statusnet/');
	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$sites' => $sitesform,
	]);
}

function statusnet_prepare_body(App $a, &$b)
{
	if ($b['item']['network'] != Protocol::STATUSNET) {
		return;
	}

	if ($b['preview']) {
		$max_char = DI::pConfig()->get(local_user(), 'statusnet', 'max_char');
		if (intval($max_char) == 0) {
			$max_char = 140;
		}

		$item = $b['item'];
		$item['plink'] = DI::baseUrl()->get() . '/display/' . $item['guid'];

		$condition = ['uri' => $item['thr-parent'], 'uid' => local_user()];
		$orig_post = Post::selectFirst(['author-link', 'uri'], $condition);
		if (DBA::isResult($orig_post)) {
			$nick = preg_replace("=https?://(.*)/(.*)=ism", "$2", $orig_post['author-link']);

			$nickname = '@[url=' . $orig_post['author-link'] . ']' . $nick . '[/url]';
			$nicknameplain = '@' . $nick;

			if ((strpos($item['body'], $nickname) === false) && (strpos($item['body'], $nicknameplain) === false)) {
				$item['body'] = $nickname . ' ' . $item['body'];
			}
		}

		$msgarr = Plaintext::getPost($item, $max_char, true, 7);
		$msg = $msgarr['text'];

		if (isset($msgarr['url']) && ($msgarr['type'] != 'photo')) {
			$msg .= ' ' . $msgarr['url'];
		}

		if (isset($msgarr['image'])) {
			$msg .= ' ' . $msgarr['image'];
		}

		$b['html'] = nl2br(htmlspecialchars($msg));
	}
}

function statusnet_cron(App $a, $b)
{
	$last = DI::config()->get('statusnet', 'last_poll');

	$poll_interval = intval(DI::config()->get('statusnet', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = STATUSNET_DEFAULT_POLL_INTERVAL;
	}

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			Logger::notice('statusnet: poll intervall not reached');
			return;
		}
	}
	Logger::notice('statusnet: cron_start');

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'statusnet', 'k' => 'mirror_posts', 'v' => true]);
	foreach ($pconfigs as $rr) {
		Logger::notice('statusnet: fetching for user ' . $rr['uid']);
		statusnet_fetchtimeline($a, $rr['uid']);
	}

	$abandon_days = intval(DI::config()->get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'statusnet', 'k' => 'import', 'v' => true]);
	foreach ($pconfigs as $rr) {
		if ($abandon_days != 0) {
			if (!DBA::exists('user', ["`uid` = ? AND `login_date` >= ?", $rr['uid'], $abandon_limit])) {
				Logger::notice('abandoned account: timeline from user ' . $rr['uid'] . ' will not be imported');
				continue;
			}
		}

		Logger::notice('statusnet: importing timeline from user ' . $rr['uid']);
		statusnet_fetchhometimeline($a, $rr['uid'], $rr['v']);
	}

	Logger::notice('statusnet: cron_end');

	DI::config()->set('statusnet', 'last_poll', time());
}

function statusnet_fetchtimeline(App $a, $uid)
{
	$ckey    = DI::pConfig()->get($uid, 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($uid, 'statusnet', 'consumersecret');
	$api     = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$otoken  = DI::pConfig()->get($uid, 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'statusnet', 'oauthsecret');
	$lastid  = DI::pConfig()->get($uid, 'statusnet', 'lastid');

	require_once 'mod/item.php';
	//  get the application name for the SN app
	//  1st try personal config, then system config and fallback to the
	//  hostname of the node if neither one is set.
	$application_name = DI::pConfig()->get($uid, 'statusnet', 'application_name');
	if ($application_name == '') {
		$application_name = DI::config()->get('statusnet', 'application_name');
	}
	if ($application_name == '') {
		$application_name = DI::baseUrl()->getHostname();
	}

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$parameters = [
		'exclude_replies' => true,
		'trim_user' => true,
		'contributor_details' => false,
		'include_rts' => false,
	];

	$first_time = ($lastid == '');

	if ($lastid != '') {
		$parameters['since_id'] = $lastid;
	}

	$items = $connection->get('statuses/user_timeline', $parameters);

	if (!is_array($items)) {
		return;
	}

	$posts = array_reverse($items);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id > $lastid)
				$lastid = $post->id;

			if ($first_time) {
				continue;
			}

			if ($post->source == 'activity') {
				continue;
			}

			if (!empty($post->retweeted_status)) {
				continue;
			}

			if ($post->in_reply_to_status_id != '') {
				continue;
			}

			if (!stristr($post->source, $application_name)) {
				$_SESSION['authenticated'] = true;
				$_SESSION['uid'] = $uid;

				unset($_REQUEST);
				$_REQUEST['api_source'] = true;
				$_REQUEST['profile_uid'] = $uid;
				//$_REQUEST['source'] = 'StatusNet';
				$_REQUEST['source'] = $post->source;
				$_REQUEST['extid'] = Protocol::STATUSNET;

				if (isset($post->id)) {
					$_REQUEST['message_id'] = Item::newURI($uid, Protocol::STATUSNET . ':' . $post->id);
				}

				//$_REQUEST['date'] = $post->created_at;

				$_REQUEST['title'] = '';

				$_REQUEST['body'] = $post->text;
				if (is_string($post->place->name)) {
					$_REQUEST['location'] = $post->place->name;
				}

				if (is_string($post->place->full_name)) {
					$_REQUEST['location'] = $post->place->full_name;
				}

				if (is_array($post->geo->coordinates)) {
					$_REQUEST['coord'] = $post->geo->coordinates[0] . ' ' . $post->geo->coordinates[1];
				}

				if (is_array($post->coordinates->coordinates)) {
					$_REQUEST['coord'] = $post->coordinates->coordinates[1] . ' ' . $post->coordinates->coordinates[0];
				}

				//print_r($_REQUEST);
				if ($_REQUEST['body'] != '') {
					Logger::notice('statusnet: posting for user ' . $uid);

					item_post($a);
				}
			}
		}
	}
	DI::pConfig()->set($uid, 'statusnet', 'lastid', $lastid);
}

function statusnet_address($contact)
{
	$hostname = Strings::normaliseLink($contact->statusnet_profile_url);
	$nickname = $contact->screen_name;

	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $contact->statusnet_profile_url);

	$address = $contact->screen_name . '@' . $hostname;

	return $address;
}

function statusnet_fetch_contact($uid, $contact, $create_user)
{
	if (empty($contact->statusnet_profile_url)) {
		return -1;
	}

	$contact_record = Contact::selectFirst([],
		['alias' => Strings::normaliseLink($contact->statusnet_profile_url), 'uid' => $uid, 'network' => Protocol::STATUSNET]);

	if (!DBA::isResult($contact_record) && !$create_user) {
		return 0;
	}

	if (DBA::isResult($contact_record) && ($contact_record['readonly'] || $contact_record['blocked'])) {
		Logger::info('statusnet_fetch_contact: Contact "' . $contact_record['nick'] . '" is blocked or readonly.');
		return -1;
	}

	if (!DBA::isResult($contact_record)) {
		$fields = [
			'uid'      => $uid,
			'created'  => DateTimeFormat::utcNow(),
			'url'      => $contact->statusnet_profile_url,
			'nurl'     => Strings::normaliseLink($contact->statusnet_profile_url),
			'addr'     => statusnet_address($contact),
			'alias'    => Strings::normaliseLink($contact->statusnet_profile_url),
			'notify'   => '',
			'poll'     => '',
			'name'     => $contact->name,
			'nick'     => $contact->screen_name,
			'photo'    => $contact->profile_image_url,
			'network'  => Protocol::STATUSNET,
			'rel'      => Contact::FRIEND,
			'priority' => 1,
			'location' => $contact->location,
			'about'    => $contact->description,
			'writable' => true,
			'blocked'  => false,
			'readonly' => false,
			'pending'  => false,
		];

		if (!Contact::insert($fields)) {
			return false;
		}

		$contact_record = Contact::selectFirst([],
			['alias' => Strings::normaliseLink($contact->statusnet_profile_url), 'uid' => $uid, 'network' => Protocol::STATUSNET]);
		if (!DBA::isResult($contact_record)) {
			return false;
		}

		$contact_id = $contact_record['id'];

		Group::addMember(User::getDefaultGroup($uid), $contact_id);

		$photos = Photo::importProfilePhoto($contact->profile_image_url, $uid, $contact_id);

		Contact::update(['photo' => $photos[0], 'thumb' => $photos[1],
			'micro' => $photos[2], 'avatar-date' => DateTimeFormat::utcNow()], ['id' => $contact_id]);
	} else {
		// update profile photos once every two weeks as we have no notification of when they change.
		//$update_photo = (($contact_record['avatar-date'] < DateTimeFormat::convert('now -2 days', '', '', )) ? true : false);
		$update_photo = ($contact_record['avatar-date'] < DateTimeFormat::utc('now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion
		if ((!$contact_record['photo']) || (!$contact_record['thumb']) || (!$contact_record['micro']) || ($update_photo)) {
			Logger::info('statusnet_fetch_contact: Updating contact ' . $contact->screen_name);

			$photos = Photo::importProfilePhoto($contact->profile_image_url, $uid, $contact_record['id']);

			Contact::update([
				'photo' => $photos[0],
				'thumb' => $photos[1],
				'micro' => $photos[2],
				'name-date' => DateTimeFormat::utcNow(),
				'uri-date' => DateTimeFormat::utcNow(),
				'avatar-date' => DateTimeFormat::utcNow(),
				'url' => $contact->statusnet_profile_url,
				'nurl' => Strings::normaliseLink($contact->statusnet_profile_url),
				'addr' => statusnet_address($contact),
				'name' => $contact->name,
				'nick' => $contact->screen_name,
				'location' => $contact->location,
				'about' => $contact->description
			], ['id' => $contact_record['id']]);
		}
	}

	return $contact_record['id'];
}

function statusnet_fetchuser(App $a, $uid, $screen_name = '', $user_id = '')
{
	$ckey    = DI::pConfig()->get($uid, 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($uid, 'statusnet', 'consumersecret');
	$api     = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$otoken  = DI::pConfig()->get($uid, 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'statusnet', 'oauthsecret');

	require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'codebirdsn.php';

	$cb = CodebirdSN::getInstance();
	$cb->setConsumerKey($ckey, $csecret);
	$cb->setToken($otoken, $osecret);

	$self = Contact::selectFirst([], ['self' => true, 'uid' => $uid]);
	if (!DBA::isResult($self)) {
		return;
	}

	$parameters = [];

	if ($screen_name != '') {
		$parameters['screen_name'] = $screen_name;
	}

	if ($user_id != '') {
		$parameters['user_id'] = $user_id;
	}

	// Fetching user data
	$user = $cb->users_show($parameters);

	if (!is_object($user)) {
		return;
	}

	$contact_id = statusnet_fetch_contact($uid, $user, true);

	return $contact_id;
}

function statusnet_createpost(App $a, $uid, $post, $self, $create_user, $only_existing_contact)
{
	Logger::info('statusnet_createpost: start');

	$api = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	$postarray = [];
	$postarray['network'] = Protocol::STATUSNET;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;

	if (!empty($post->retweeted_status)) {
		$content = $post->retweeted_status;
		statusnet_fetch_contact($uid, $content->user, false);
	} else {
		$content = $post;
	}

	$postarray['uri'] = $hostname . '::' . $content->id;

	if (Post::exists(['extid' => $postarray['uri'], 'uid' => $uid])) {
		return [];
	}

	$contactId = 0;

	if (!empty($content->in_reply_to_status_id)) {
		$thr_parent = $hostname . '::' . $content->in_reply_to_status_id;

		$item = Post::selectFirst(['uri'], ['uri' => $thr_parent, 'uid' => $uid]);
		if (!DBA::isResult($item)) {
			$item = Post::selectFirst(['uri'], ['extid' => $thr_parent, 'uid' => $uid]);
		}

		if (DBA::isResult($item)) {
			$postarray['thr-parent'] = $item['uri'];
			$postarray['object-type'] = Activity\ObjectType::COMMENT;
		} else {
			$postarray['object-type'] = Activity\ObjectType::NOTE;
		}

		// Is it me?
		$own_url = DI::pConfig()->get($uid, 'statusnet', 'own_url');

		if ($content->user->id == $own_url) {
			$self = DBA::selectFirst('contact', [], ['self' => true, 'uid' => $uid]);
			if (DBA::isResult($self)) {
				$contactId = $self['id'];

				$postarray['owner-name'] = $self['name'];
				$postarray['owner-link'] = $self['url'];
				$postarray['owner-avatar'] = $self['photo'];
			} else {
				return [];
			}
		}
		// Don't create accounts of people who just comment something
		$create_user = false;
	} else {
		$postarray['object-type'] = Activity\ObjectType::NOTE;
	}

	if ($contactId == 0) {
		$contactId = statusnet_fetch_contact($uid, $post->user, $create_user);
		$postarray['owner-name'] = $post->user->name;
		$postarray['owner-link'] = $post->user->statusnet_profile_url;
		$postarray['owner-avatar'] = $post->user->profile_image_url;
	}
	if (($contactId == 0) && !$only_existing_contact) {
		$contactId = $self['id'];
	} elseif ($contactId <= 0) {
		return [];
	}

	$postarray['contact-id'] = $contactId;

	$postarray['verb'] = Activity::POST;

	$postarray['author-name'] = $content->user->name;
	$postarray['author-link'] = $content->user->statusnet_profile_url;
	$postarray['author-avatar'] = $content->user->profile_image_url;

	// To-Do: Maybe unreliable? Can the api be entered without trailing "/"?
	$hostname = str_replace('/api/', '/notice/', DI::pConfig()->get($uid, 'statusnet', 'baseapi'));

	$postarray['plink'] = $hostname . $content->id;
	$postarray['app'] = strip_tags($content->source);

	if ($content->user->protected) {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self['id'] . '>';
	}

	$postarray['body'] = HTML::toBBCode($content->statusnet_html);

	$postarray['body'] = statusnet_convertmsg($a, $postarray['body']);

	$postarray['created'] = DateTimeFormat::utc($content->created_at);
	$postarray['edited'] = DateTimeFormat::utc($content->created_at);

	if (!empty($content->place->name)) {
		$postarray['location'] = $content->place->name;
	}

	if (!empty($content->place->full_name)) {
		$postarray['location'] = $content->place->full_name;
	}

	if (!empty($content->geo->coordinates)) {
		$postarray['coord'] = $content->geo->coordinates[0] . ' ' . $content->geo->coordinates[1];
	}

	if (!empty($content->coordinates->coordinates)) {
		$postarray['coord'] = $content->coordinates->coordinates[1] . ' ' . $content->coordinates->coordinates[0];
	}

	Logger::info('statusnet_createpost: end');

	return $postarray;
}

function statusnet_fetchhometimeline(App $a, $uid, $mode = 1)
{
	$conversations = [];

	$ckey    = DI::pConfig()->get($uid, 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($uid, 'statusnet', 'consumersecret');
	$api     = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$otoken  = DI::pConfig()->get($uid, 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'statusnet', 'oauthsecret');
	$create_user = DI::pConfig()->get($uid, 'statusnet', 'create_user');

	// "create_user" is deactivated, since currently you cannot add users manually by now
	$create_user = true;

	Logger::info('statusnet_fetchhometimeline: Fetching for user ' . $uid);

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$own_contact = statusnet_fetch_own_contact($a, $uid);

	if (empty($own_contact)) {
		return;
	}

	$contact = Contact::selectFirst([], ['id' => $own_contact, 'uid' => $uid]);
	if (DBA::isResult($contact)) {
		$nick = $contact['nick'];
	} else {
		Logger::info('statusnet_fetchhometimeline: Own GNU Social contact not found for user ' . $uid);
		return;
	}

	$self = Contact::selectFirst([], ['self' => true, 'uid' => $uid]);
	if (!DBA::isResult($self)) {
		Logger::info('statusnet_fetchhometimeline: Own contact not found for user ' . $uid);
		return;
	}

	$user = User::getById($uid);
	if (!DBA::isResult($user)) {
		Logger::info('statusnet_fetchhometimeline: Own user not found for user ' . $uid);
		return;
	}

	$parameters = [
		'exclude_replies' => false,
		'trim_user' => false,
		'contributor_details' => true,
		'include_rts' => true,
		//'count' => 200,
	];

	if ($mode == 1) {
		// Fetching timeline
		$lastid = DI::pConfig()->get($uid, 'statusnet', 'lasthometimelineid');
		//$lastid = 1;

		$first_time = ($lastid == '');

		if ($lastid != '') {
			$parameters['since_id'] = $lastid;
		}

		$items = $connection->get('statuses/home_timeline', $parameters);

		if (!is_array($items)) {
			if (is_object($items) && isset($items->error)) {
				$errormsg = $items->error;
			} elseif (is_object($items)) {
				$errormsg = print_r($items, true);
			} elseif (is_string($items) || is_float($items) || is_int($items)) {
				$errormsg = $items;
			} else {
				$errormsg = 'Unknown error';
			}

			Logger::info('statusnet_fetchhometimeline: Error fetching home timeline: ' . $errormsg);
			return;
		}

		$posts = array_reverse($items);

		Logger::info('statusnet_fetchhometimeline: Fetching timeline for user ' . $uid . ' ' . sizeof($posts) . ' items');

		if (count($posts)) {
			foreach ($posts as $post) {

				if ($post->id > $lastid) {
					$lastid = $post->id;
				}

				if ($first_time) {
					continue;
				}

				if (isset($post->statusnet_conversation_id)) {
					if (!isset($conversations[$post->statusnet_conversation_id])) {
						statusnet_complete_conversation($a, $uid, $self, $create_user, $nick, $post->statusnet_conversation_id);
						$conversations[$post->statusnet_conversation_id] = $post->statusnet_conversation_id;
					}
				} else {
					$postarray = statusnet_createpost($a, $uid, $post, $self, $create_user, true);

					if (trim($postarray['body']) == '') {
						continue;
					}

					$item = Item::insert($postarray);
					$postarray['id'] = $item;

					Logger::notice('statusnet_fetchhometimeline: User ' . $self['nick'] . ' posted home timeline item ' . $item);
				}
			}
		}
		DI::pConfig()->set($uid, 'statusnet', 'lasthometimelineid', $lastid);
	}

	// Fetching mentions
	$lastid = DI::pConfig()->get($uid, 'statusnet', 'lastmentionid');
	$first_time = ($lastid == '');

	if ($lastid != '') {
		$parameters['since_id'] = $lastid;
	}

	$items = $connection->get('statuses/mentions_timeline', $parameters);

	if (!is_array($items)) {
		Logger::info('statusnet_fetchhometimeline: Error fetching mentions: ' . print_r($items, true));
		return;
	}

	$posts = array_reverse($items);

	Logger::info('statusnet_fetchhometimeline: Fetching mentions for user ' . $uid . ' ' . sizeof($posts) . ' items');

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id > $lastid) {
				$lastid = $post->id;
			}

			if ($first_time) {
				continue;
			}

			$postarray = statusnet_createpost($a, $uid, $post, $self, false, false);

			if (isset($post->statusnet_conversation_id)) {
				if (!isset($conversations[$post->statusnet_conversation_id])) {
					statusnet_complete_conversation($a, $uid, $self, $create_user, $nick, $post->statusnet_conversation_id);
					$conversations[$post->statusnet_conversation_id] = $post->statusnet_conversation_id;
				}
			} else {
				if (trim($postarray['body']) == '') {
					continue;
				}

				$item = Item::insert($postarray);

				Logger::notice('statusnet_fetchhometimeline: User ' . $self['nick'] . ' posted mention timeline item ' . $item);
			}
		}
	}

	DI::pConfig()->set($uid, 'statusnet', 'lastmentionid', $lastid);
}

function statusnet_complete_conversation(App $a, $uid, $self, $create_user, $nick, $conversation)
{
	$ckey    = DI::pConfig()->get($uid, 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($uid, 'statusnet', 'consumersecret');
	$api     = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$otoken  = DI::pConfig()->get($uid, 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'statusnet', 'oauthsecret');
	$own_url = DI::pConfig()->get($uid, 'statusnet', 'own_url');

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$parameters['count'] = 200;

	$items = $connection->get('statusnet/conversation/' . $conversation, $parameters);
	if (is_array($items)) {
		$posts = array_reverse($items);

		foreach ($posts as $post) {
			$postarray = statusnet_createpost($a, $uid, $post, $self, false, false);

			if (empty($postarray['body'])) {
				continue;
			}

			$item = Item::insert($postarray);
			$postarray['id'] = $item;

			Logger::notice('statusnet_complete_conversation: User ' . $self['nick'] . ' posted home timeline item ' . $item);
		}
	}
}

function statusnet_convertmsg(App $a, $body)
{
	$body = preg_replace("=\[url\=https?://([0-9]*).([0-9]*).([0-9]*).([0-9]*)/([0-9]*)\](.*?)\[\/url\]=ism", "$1.$2.$3.$4/$5", $body);

	$URLSearchString = '^\[\]';
	$links = preg_match_all("/[^!#@]\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER);

	$footer = $footerurl = $footerlink = $type = '';

	if ($links) {
		foreach ($matches AS $match) {
			$search = '[url=' . $match[1] . ']' . $match[2] . '[/url]';

			Logger::info('statusnet_convertmsg: expanding url ' . $match[1]);

			try {
				$expanded_url = DI::httpClient()->finalUrl($match[1]);
			} catch (TransferException $exception) {
				Logger::notice('statusnet_convertmsg: Couldn\'t get final URL.', ['url' => $match[1], 'exception' => $exception]);
				$expanded_url = $match[1];
			}

			Logger::info('statusnet_convertmsg: fetching data for ' . $expanded_url);

			$oembed_data = OEmbed::fetchURL($expanded_url, true);

			Logger::info('statusnet_convertmsg: fetching data: done');

			if ($type == '') {
				$type = $oembed_data->type;
			}

			if ($oembed_data->type == 'video') {
				//$body = str_replace($search, '[video]'.$expanded_url.'[/video]', $body);
				$type = $oembed_data->type;
				$footerurl = $expanded_url;
				$footerlink = '[url=' . $expanded_url . ']' . $expanded_url . '[/url]';

				$body = str_replace($search, $footerlink, $body);
			} elseif (($oembed_data->type == 'photo') && isset($oembed_data->url)) {
				$body = str_replace($search, '[url=' . $expanded_url . '][img]' . $oembed_data->url . '[/img][/url]', $body);
			} elseif ($oembed_data->type != 'link') {
				$body = str_replace($search, '[url=' . $expanded_url . ']' . $expanded_url . '[/url]', $body);
			} else {
				$img_str = DI::httpClient()->fetch($expanded_url, HttpClientAccept::DEFAULT, 4);

				$tempfile = tempnam(System::getTempPath(), 'cache');
				file_put_contents($tempfile, $img_str);
				$mime = mime_content_type($tempfile);
				unlink($tempfile);

				if (substr($mime, 0, 6) == 'image/') {
					$type = 'photo';
					$body = str_replace($search, '[img]' . $expanded_url . '[/img]', $body);
				} else {
					$type = $oembed_data->type;
					$footerurl = $expanded_url;
					$footerlink = '[url=' . $expanded_url . ']' . $expanded_url . '[/url]';

					$body = str_replace($search, $footerlink, $body);
				}
			}
		}

		if ($footerurl != '') {
			$footer = "\n" . PageInfo::getFooterFromUrl($footerurl);
		}

		if (($footerlink != '') && (trim($footer) != '')) {
			$removedlink = trim(str_replace($footerlink, '', $body));

			if (($removedlink == '') || strstr($body, $removedlink)) {
				$body = $removedlink;
			}

			$body .= $footer;
		}
	}

	return $body;
}

function statusnet_fetch_own_contact(App $a, $uid)
{
	$ckey    = DI::pConfig()->get($uid, 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($uid, 'statusnet', 'consumersecret');
	$api     = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$otoken  = DI::pConfig()->get($uid, 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'statusnet', 'oauthsecret');
	$own_url = DI::pConfig()->get($uid, 'statusnet', 'own_url');

	$contact_id = 0;

	if ($own_url == '') {
		$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

		// Fetching user data
		$user = $connection->get('account/verify_credentials');

		if (empty($user)) {
			return false;
		}

		DI::pConfig()->set($uid, 'statusnet', 'own_url', Strings::normaliseLink($user->statusnet_profile_url));

		$contact_id = statusnet_fetch_contact($uid, $user, true);
	} else {
		$contact = Contact::selectFirst([], ['uid' => $uid, 'alias' => $own_url]);
		if (DBA::isResult($contact)) {
			$contact_id = $contact['id'];
		} else {
			DI::pConfig()->delete($uid, 'statusnet', 'own_url');
		}
	}
	return $contact_id;
}

function statusnet_is_retweet(App $a, $uid, $body)
{
	$body = trim($body);

	// Skip if it isn't a pure repeated messages
	// Does it start with a share?
	if (strpos($body, '[share') > 0) {
		return false;
	}

	// Does it end with a share?
	if (strlen($body) > (strrpos($body, '[/share]') + 8)) {
		return false;
	}

	$attributes = preg_replace("/\[share(.*?)\]\s?(.*?)\s?\[\/share\]\s?/ism", "$1", $body);
	// Skip if there is no shared message in there
	if ($body == $attributes) {
		return false;
	}

	$link = '';
	preg_match("/link='(.*?)'/ism", $attributes, $matches);
	if (!empty($matches[1])) {
		$link = $matches[1];
	}

	preg_match('/link="(.*?)"/ism', $attributes, $matches);
	if (!empty($matches[1])) {
		$link = $matches[1];
	}

	$ckey    = DI::pConfig()->get($uid, 'statusnet', 'consumerkey');
	$csecret = DI::pConfig()->get($uid, 'statusnet', 'consumersecret');
	$api     = DI::pConfig()->get($uid, 'statusnet', 'baseapi');
	$otoken  = DI::pConfig()->get($uid, 'statusnet', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'statusnet', 'oauthsecret');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	$id = preg_replace("=https?://" . $hostname . "/notice/(.*)=ism", "$1", $link);

	if ($id == $link) {
		return false;
	}

	Logger::info('statusnet_is_retweet: Retweeting id ' . $id . ' for user ' . $uid);

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$result = $connection->post('statuses/retweet/' . $id);

	Logger::info('statusnet_is_retweet: result ' . print_r($result, true));

	return isset($result->id);
}
