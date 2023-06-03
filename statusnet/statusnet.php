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
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Model\User;

function statusnet_install()
{
	//  we need some hooks, for the configuration and for sending tweets
	Hook::register('connector_settings', 'addon/statusnet/statusnet.php', 'statusnet_settings');
	Hook::register('connector_settings_post', 'addon/statusnet/statusnet.php', 'statusnet_settings_post');
	Hook::register('notifier_normal', 'addon/statusnet/statusnet.php', 'statusnet_post_hook');
	Hook::register('hook_fork', 'addon/statusnet/statusnet.php', 'statusnet_hook_fork');
	Hook::register('post_local', 'addon/statusnet/statusnet.php', 'statusnet_post_local');
	Hook::register('jot_networks', 'addon/statusnet/statusnet.php', 'statusnet_jot_nets');
	Logger::notice('installed GNU Social');
}

function statusnet_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'statusnet_enable',
				DI::l10n()->t('Post to GNU Social'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'post_by_default')
			]
		];
	}
}

function statusnet_settings_post($post)
{
	if (!DI::userSession()->getLocalUserId()) {
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
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'statusnet', 'consumerkey');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'statusnet', 'consumersecret');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'statusnet', 'post');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'statusnet', 'post_by_default');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'statusnet', 'oauthtoken');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'statusnet', 'oauthsecret');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'statusnet', 'baseapi');
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
						DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'consumerkey', $asn['consumerkey']);
						DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'consumersecret', $asn['consumersecret']);
						DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'baseapi', $asn['apiurl']);
						//DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'application_name', $asn['applicationname'] );
					} else {
						DI::sysmsg()->addNotice(DI::l10n()->t('Please contact your site administrator.<br />The provided API URL is not valid.') . '<br />' . $asn['apiurl']);
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
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'baseapi', $apibase);
					//DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'application_name', $_POST['statusnet-applicationname'] );
				} else {
					//  the API path is not correct, maybe missing trailing / ?
					$apibase = $apibase . '/';
					$c = DI::httpClient()->fetch($apibase . 'statusnet/version.xml');
					if (strlen($c) > 0) {
						//  ok the API path is now correct, let's save the settings
						DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
						DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
						DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'baseapi', $apibase);
					} else {
						//  still not the correct API base, let's do noting
						DI::sysmsg()->addNotice(DI::l10n()->t('We could not contact the GNU Social API with the Path you entered.'));
					}
				}
			} else {
				if (isset($_POST['statusnet-pin'])) {
					//  if the user supplied us with a PIN from GNU Social, let the magic of OAuth happen
					$api = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'baseapi');
					$ckey = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'consumerkey');
					$csecret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'consumersecret');
					//  the token and secret for which the PIN was generated were hidden in the settings
					//  form as token and token2, we need a new connection to GNU Social using these token
					//  and secret to request a Access Token with the PIN
					$connection = new StatusNetOAuth($api, $ckey, $csecret, $_POST['statusnet-token'], $_POST['statusnet-token2']);
					$token = $connection->getAccessToken($_POST['statusnet-pin']);
					//  ok, now that we have the Access Token, save them in the user config
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'oauthtoken', $token['oauth_token']);
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'oauthsecret', $token['oauth_token_secret']);
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'post', 1);
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'post_taglinks', 1);
					//  reload the Addon Settings page, if we don't do it see Bug #42
				} else {
					//  if no PIN is supplied in the POST variables, the user has changed the setting
					//  to post a dent for every new __public__ posting to the wall
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'post', intval($_POST['statusnet-enable']));
					DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'statusnet', 'post_by_default', intval($_POST['statusnet-default']));
				}
			}
		}
	}
}

function statusnet_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	DI::page()->registerStylesheet(__DIR__ . '/statusnet.css', 'all');

	/*	 * *
	 * 1) Check that we have a base api url and a consumer key & secret
	 * 2) If no OAuthtoken & stuff is present, generate button to get some
	 *    allow the user to cancel the connection process at this step
	 * 3) Checkbox for "Send public notices (respect size limitation)
	 */
	$baseapi     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'baseapi');
	$ckey        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'consumerkey');
	$csecret     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'consumersecret');
	$otoken      = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'oauthtoken');
	$osecret     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'oauthsecret');
	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'post', false);
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'post_by_default', false);

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

			$user = User::getById(DI::userSession()->getLocalUserId());
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

		'$authenticate_url' => DI::baseUrl() . '/statusnet/connect',

		'$consumerkey'    => ['statusnet-consumerkey', DI::l10n()->t('OAuth Consumer Key'), '', '', false, ' size="35'],
		'$consumersecret' => ['statusnet-consumersecret', DI::l10n()->t('OAuth Consumer Secret'), '', '', false, ' size="35'],

		'$baseapi' => ['statusnet-baseapi', DI::l10n()->t('Base API Path (remember the trailing /)'), '', '', false, ' size="35'],
		'$pin'     => ['statusnet-pin', DI::l10n()->t('Copy the security code from GNU Social here')],

		'$enable'      => ['statusnet-enabled', DI::l10n()->t('Allow posting to GNU Social'), $enabled, DI::l10n()->t('If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.')],
		'$default'     => ['statusnet-default', DI::l10n()->t('Post to GNU Social by default'), $def_enabled],
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

function statusnet_hook_fork(array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || ($post['created'] !== $post['edited']) || strpos($post['postopts'] ?? '', 'statusnet') === false || ($post['parent'] != $post['id']) || $post['private']) {
		$b['execute'] = false;
		return;
	}
}

function statusnet_post_local(array &$b)
{
	if ($b['edit']) {
		return;
	}

	if (!DI::userSession()->getLocalUserId() || (DI::userSession()->getLocalUserId() != $b['uid'])) {
		return;
	}

	$statusnet_post = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'post');
	$statusnet_enable = (($statusnet_post && !empty($_REQUEST['statusnet_enable'])) ? intval($_REQUEST['statusnet_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'statusnet', 'post_by_default'))) {
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

function statusnet_post_hook(array &$b)
{
	/**
	 * Post to GNU Social
	 */
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], DI::contentItem()->addSharedPost($b));

	$api = DI::pConfig()->get($b['uid'], 'statusnet', 'baseapi');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	if ($b['private'] || !strstr($b['postopts'], 'statusnet')) {
		return;
	}

	// Dont't post if the post doesn't belong to us.
	// This is a check for group postings
	$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
	if ($b['contact-id'] != $self['id']) {
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

function statusnet_addon_admin_post()
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

function statusnet_addon_admin(string &$o)
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
