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
use Friendica\Content\Text\HTML;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Database\DBA;
use Friendica\Model\Contact;
use Friendica\Model\GContact;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\ItemContent;
use Friendica\Model\Photo;
use Friendica\Model\User;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;
use Friendica\Util\Strings;

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
	Logger::log("installed GNU Social");
}

function statusnet_uninstall()
{
	Hook::unregister('connector_settings', 'addon/statusnet/statusnet.php', 'statusnet_settings');
	Hook::unregister('connector_settings_post', 'addon/statusnet/statusnet.php', 'statusnet_settings_post');
	Hook::unregister('notifier_normal', 'addon/statusnet/statusnet.php', 'statusnet_post_hook');
	Hook::unregister('hook_fork', 'addon/statusnet/statusnet.php', 'statusnet_hook_fork');
	Hook::unregister('post_local', 'addon/statusnet/statusnet.php', 'statusnet_post_local');
	Hook::unregister('jot_networks', 'addon/statusnet/statusnet.php', 'statusnet_jot_nets');
	Hook::unregister('cron', 'addon/statusnet/statusnet.php', 'statusnet_cron');
	Hook::unregister('prepare_body', 'addon/statusnet/statusnet.php', 'statusnet_prepare_body');
	Hook::unregister('check_item_notification', 'addon/statusnet/statusnet.php', 'statusnet_check_item_notification');

	// old setting - remove only
	Hook::unregister('post_local_end', 'addon/statusnet/statusnet.php', 'statusnet_post_hook');
	Hook::unregister('addon_settings', 'addon/statusnet/statusnet.php', 'statusnet_settings');
	Hook::unregister('addon_settings_post', 'addon/statusnet/statusnet.php', 'statusnet_settings_post');
}

function statusnet_check_item_notification(App $a, &$notification_data)
{
	if (PConfig::get($notification_data["uid"], 'statusnet', 'post')) {
		$notification_data["profiles"][] = PConfig::get($notification_data["uid"], 'statusnet', 'own_url');
	}
}

function statusnet_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (PConfig::get(local_user(), 'statusnet', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'statusnet_enable',
				L10n::t('Post to GNU Social'),
				PConfig::get(local_user(), 'statusnet', 'post_by_default')
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
	if (empty($_POST['statusnet-submit'])) {
		return;
	}

	if (isset($_POST['statusnet-disconnect'])) {
		/*		 * *
		 * if the GNU Social-disconnect checkbox is set, clear the GNU Social configuration
		 */
		PConfig::delete(local_user(), 'statusnet', 'consumerkey');
		PConfig::delete(local_user(), 'statusnet', 'consumersecret');
		PConfig::delete(local_user(), 'statusnet', 'post');
		PConfig::delete(local_user(), 'statusnet', 'post_by_default');
		PConfig::delete(local_user(), 'statusnet', 'oauthtoken');
		PConfig::delete(local_user(), 'statusnet', 'oauthsecret');
		PConfig::delete(local_user(), 'statusnet', 'baseapi');
		PConfig::delete(local_user(), 'statusnet', 'lastid');
		PConfig::delete(local_user(), 'statusnet', 'mirror_posts');
		PConfig::delete(local_user(), 'statusnet', 'import');
		PConfig::delete(local_user(), 'statusnet', 'create_user');
		PConfig::delete(local_user(), 'statusnet', 'own_url');
	} else {
		if (isset($_POST['statusnet-preconf-apiurl'])) {
			/*			 * *
			 * If the user used one of the preconfigured GNU Social server credentials
			 * use them. All the data are available in the global config.
			 * Check the API Url never the less and blame the admin if it's not working ^^
			 */
			$globalsn = Config::get('statusnet', 'sites');
			foreach ($globalsn as $asn) {
				if ($asn['apiurl'] == $_POST['statusnet-preconf-apiurl']) {
					$apibase = $asn['apiurl'];
					$c = Network::fetchUrl($apibase . 'statusnet/version.xml');
					if (strlen($c) > 0) {
						PConfig::set(local_user(), 'statusnet', 'consumerkey', $asn['consumerkey']);
						PConfig::set(local_user(), 'statusnet', 'consumersecret', $asn['consumersecret']);
						PConfig::set(local_user(), 'statusnet', 'baseapi', $asn['apiurl']);
						//PConfig::set(local_user(), 'statusnet', 'application_name', $asn['applicationname'] );
					} else {
						notice(L10n::t('Please contact your site administrator.<br />The provided API URL is not valid.') . EOL . $asn['apiurl'] . EOL);
					}
				}
			}
			$a->internalRedirect('settings/connectors');
		} else {
			if (isset($_POST['statusnet-consumersecret'])) {
				//  check if we can reach the API of the GNU Social server
				//  we'll check the API Version for that, if we don't get one we'll try to fix the path but will
				//  resign quickly after this one try to fix the path ;-)
				$apibase = $_POST['statusnet-baseapi'];
				$c = Network::fetchUrl($apibase . 'statusnet/version.xml');
				if (strlen($c) > 0) {
					//  ok the API path is correct, let's save the settings
					PConfig::set(local_user(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
					PConfig::set(local_user(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
					PConfig::set(local_user(), 'statusnet', 'baseapi', $apibase);
					//PConfig::set(local_user(), 'statusnet', 'application_name', $_POST['statusnet-applicationname'] );
				} else {
					//  the API path is not correct, maybe missing trailing / ?
					$apibase = $apibase . '/';
					$c = Network::fetchUrl($apibase . 'statusnet/version.xml');
					if (strlen($c) > 0) {
						//  ok the API path is now correct, let's save the settings
						PConfig::set(local_user(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
						PConfig::set(local_user(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
						PConfig::set(local_user(), 'statusnet', 'baseapi', $apibase);
					} else {
						//  still not the correct API base, let's do noting
						notice(L10n::t('We could not contact the GNU Social API with the Path you entered.') . EOL);
					}
				}
				$a->internalRedirect('settings/connectors');
			} else {
				if (isset($_POST['statusnet-pin'])) {
					//  if the user supplied us with a PIN from GNU Social, let the magic of OAuth happen
					$api = PConfig::get(local_user(), 'statusnet', 'baseapi');
					$ckey = PConfig::get(local_user(), 'statusnet', 'consumerkey');
					$csecret = PConfig::get(local_user(), 'statusnet', 'consumersecret');
					//  the token and secret for which the PIN was generated were hidden in the settings
					//  form as token and token2, we need a new connection to GNU Social using these token
					//  and secret to request a Access Token with the PIN
					$connection = new StatusNetOAuth($api, $ckey, $csecret, $_POST['statusnet-token'], $_POST['statusnet-token2']);
					$token = $connection->getAccessToken($_POST['statusnet-pin']);
					//  ok, now that we have the Access Token, save them in the user config
					PConfig::set(local_user(), 'statusnet', 'oauthtoken', $token['oauth_token']);
					PConfig::set(local_user(), 'statusnet', 'oauthsecret', $token['oauth_token_secret']);
					PConfig::set(local_user(), 'statusnet', 'post', 1);
					PConfig::set(local_user(), 'statusnet', 'post_taglinks', 1);
					//  reload the Addon Settings page, if we don't do it see Bug #42
					$a->internalRedirect('settings/connectors');
				} else {
					//  if no PIN is supplied in the POST variables, the user has changed the setting
					//  to post a dent for every new __public__ posting to the wall
					PConfig::set(local_user(), 'statusnet', 'post', intval($_POST['statusnet-enable']));
					PConfig::set(local_user(), 'statusnet', 'post_by_default', intval($_POST['statusnet-default']));
					PConfig::set(local_user(), 'statusnet', 'mirror_posts', intval($_POST['statusnet-mirror']));
					PConfig::set(local_user(), 'statusnet', 'import', intval($_POST['statusnet-import']));
					PConfig::set(local_user(), 'statusnet', 'create_user', intval($_POST['statusnet-create_user']));

					if (!intval($_POST['statusnet-mirror']))
						PConfig::delete(local_user(), 'statusnet', 'lastid');

					info(L10n::t('GNU Social settings updated.') . EOL);
				}
			}
		}
	}
}

function statusnet_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/statusnet/statusnet.css' . '" media="all" />' . "\r\n";
	/*	 * *
	 * 1) Check that we have a base api url and a consumer key & secret
	 * 2) If no OAuthtoken & stuff is present, generate button to get some
	 *    allow the user to cancel the connection process at this step
	 * 3) Checkbox for "Send public notices (respect size limitation)
	 */
	$api     = PConfig::get(local_user(), 'statusnet', 'baseapi');
	$ckey    = PConfig::get(local_user(), 'statusnet', 'consumerkey');
	$csecret = PConfig::get(local_user(), 'statusnet', 'consumersecret');
	$otoken  = PConfig::get(local_user(), 'statusnet', 'oauthtoken');
	$osecret = PConfig::get(local_user(), 'statusnet', 'oauthsecret');
	$enabled = PConfig::get(local_user(), 'statusnet', 'post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$defenabled = PConfig::get(local_user(), 'statusnet', 'post_by_default');
	$defchecked = (($defenabled) ? ' checked="checked" ' : '');
	$mirrorenabled = PConfig::get(local_user(), 'statusnet', 'mirror_posts');
	$mirrorchecked = (($mirrorenabled) ? ' checked="checked" ' : '');
	$import = PConfig::get(local_user(), 'statusnet', 'import');
	$importselected = ["", "", ""];
	$importselected[$import] = ' selected="selected"';
	//$importenabled = PConfig::get(local_user(),'statusnet','import');
	//$importchecked = (($importenabled) ? ' checked="checked" ' : '');
	$create_userenabled = PConfig::get(local_user(), 'statusnet', 'create_user');
	$create_userchecked = (($create_userenabled) ? ' checked="checked" ' : '');

	$css = (($enabled) ? '' : '-disabled');

	$s .= '<span id="settings_statusnet_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_statusnet_expanded\'); openClose(\'settings_statusnet_inflated\');">';
	$s .= '<img class="connector' . $css . '" src="images/gnusocial.png" /><h3 class="connector">' . L10n::t('GNU Social Import/Export/Mirror') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_statusnet_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_statusnet_expanded\'); openClose(\'settings_statusnet_inflated\');">';
	$s .= '<img class="connector' . $css . '" src="images/gnusocial.png" /><h3 class="connector">' . L10n::t('GNU Social Import/Export/Mirror') . '</h3>';
	$s .= '</span>';

	if ((!$ckey) && (!$csecret)) {
		/*		 * *
		 * no consumer keys
		 */
		$globalsn = Config::get('statusnet', 'sites');
		/*		 * *
		 * lets check if we have one or more globally configured GNU Social
		 * server OAuth credentials in the configuration. If so offer them
		 * with a little explanation to the user as choice - otherwise
		 * ignore this option entirely.
		 */
		if (!$globalsn == null) {
			$s .= '<h4>' . L10n::t('Globally Available GNU Social OAuthKeys') . '</h4>';
			$s .= '<p>' . L10n::t("There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance \x28see below\x29.") . '</p>';
			$s .= '<div id="statusnet-preconf-wrapper">';
			foreach ($globalsn as $asn) {
				$s .= '<input type="radio" name="statusnet-preconf-apiurl" value="' . $asn['apiurl'] . '">' . $asn['sitename'] . '<br />';
			}
			$s .= '<p></p><div class="clear"></div></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
		}
		$s .= '<h4>' . L10n::t('Provide your own OAuth Credentials') . '</h4>';
		$s .= '<p>' . L10n::t('No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation.') . '</p>';
		$s .= '<div id="statusnet-consumer-wrapper">';
		$s .= '<label id="statusnet-consumerkey-label" for="statusnet-consumerkey">' . L10n::t('OAuth Consumer Key') . '</label>';
		$s .= '<input id="statusnet-consumerkey" type="text" name="statusnet-consumerkey" size="35" /><br />';
		$s .= '<div class="clear"></div>';
		$s .= '<label id="statusnet-consumersecret-label" for="statusnet-consumersecret">' . L10n::t('OAuth Consumer Secret') . '</label>';
		$s .= '<input id="statusnet-consumersecret" type="text" name="statusnet-consumersecret" size="35" /><br />';
		$s .= '<div class="clear"></div>';
		$s .= '<label id="statusnet-baseapi-label" for="statusnet-baseapi">' . L10n::t("Base API Path \x28remember the trailing /\x29") . '</label>';
		$s .= '<input id="statusnet-baseapi" type="text" name="statusnet-baseapi" size="35" /><br />';
		$s .= '<div class="clear"></div>';
		//$s .= '<label id="statusnet-applicationname-label" for="statusnet-applicationname">'.L10n::t('GNU Socialapplication name').'</label>';
		//$s .= '<input id="statusnet-applicationname" type="text" name="statusnet-applicationname" size="35" /><br />';
		$s .= '<p></p><div class="clear"></div>';
		$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
		$s .= '</div>';
	} else {
		/*		 * *
		 * ok we have a consumer key pair now look into the OAuth stuff
		 */
		if ((!$otoken) && (!$osecret)) {
			/*			 * *
			 * the user has not yet connected the account to GNU Social
			 * get a temporary OAuth key/secret pair and display a button with
			 * which the user can request a PIN to connect the account to a
			 * account at GNU Social
			 */
			$connection = new StatusNetOAuth($api, $ckey, $csecret);
			$request_token = $connection->getRequestToken('oob');
			$token = $request_token['oauth_token'];
			/*			 * *
			 *  make some nice form
			 */
			$s .= '<p>' . L10n::t('To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.') . '</p>';
			$s .= '<a href="' . $connection->getAuthorizeURL($token, False) . '" target="_statusnet"><img src="addon/statusnet/signinwithstatusnet.png" alt="' . L10n::t('Log in with GNU Social') . '"></a>';
			$s .= '<div id="statusnet-pin-wrapper">';
			$s .= '<label id="statusnet-pin-label" for="statusnet-pin">' . L10n::t('Copy the security code from GNU Social here') . '</label>';
			$s .= '<input id="statusnet-pin" type="text" name="statusnet-pin" />';
			$s .= '<input id="statusnet-token" type="hidden" name="statusnet-token" value="' . $token . '" />';
			$s .= '<input id="statusnet-token2" type="hidden" name="statusnet-token2" value="' . $request_token['oauth_token_secret'] . '" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
			$s .= '<h4>' . L10n::t('Cancel Connection Process') . '</h4>';
			$s .= '<div id="statusnet-cancel-wrapper">';
			$s .= '<p>' . L10n::t('Current GNU Social API is') . ': ' . $api . '</p>';
			$s .= '<label id="statusnet-cancel-label" for="statusnet-cancel">' . L10n::t('Cancel GNU Social Connection') . '</label>';
			$s .= '<input id="statusnet-cancel" type="checkbox" name="statusnet-disconnect" value="1" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
		} else {
			/*			 * *
			 *  we have an OAuth key / secret pair for the user
			 *  so let's give a chance to disable the postings to GNU Social
			 */
			$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);
			$details = $connection->get('account/verify_credentials');
			if (!empty($details)) {
				$s .= '<div id="statusnet-info" ><img id="statusnet-avatar" src="' . $details->profile_image_url . '" /><p id="statusnet-info-block">' . L10n::t('Currently connected to: ') . '<a href="' . $details->statusnet_profile_url . '" target="_statusnet">' . $details->screen_name . '</a><br /><em>' . $details->description . '</em></p></div>';
			}
			$s .= '<p>' . L10n::t('If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.') . '</p>';
			if ($a->user['hidewall']) {
				$s .= '<p>' . L10n::t('<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.') . '</p>';
			}
			$s .= '<div id="statusnet-enable-wrapper">';
			$s .= '<label id="statusnet-enable-label" for="statusnet-checkbox">' . L10n::t('Allow posting to GNU Social') . '</label>';
			$s .= '<input id="statusnet-checkbox" type="checkbox" name="statusnet-enable" value="1" ' . $checked . '/>';
			$s .= '<div class="clear"></div>';
			$s .= '<label id="statusnet-default-label" for="statusnet-default">' . L10n::t('Send public postings to GNU Social by default') . '</label>';
			$s .= '<input id="statusnet-default" type="checkbox" name="statusnet-default" value="1" ' . $defchecked . '/>';
			$s .= '<div class="clear"></div>';

			$s .= '<label id="statusnet-mirror-label" for="statusnet-mirror">' . L10n::t('Mirror all posts from GNU Social that are no replies or repeated messages') . '</label>';
			$s .= '<input id="statusnet-mirror" type="checkbox" name="statusnet-mirror" value="1" ' . $mirrorchecked . '/>';

			$s .= '<div class="clear"></div>';
			$s .= '</div>';

			$s .= '<label id="statusnet-import-label" for="statusnet-import">' . L10n::t('Import the remote timeline') . '</label>';
			//$s .= '<input id="statusnet-import" type="checkbox" name="statusnet-import" value="1" '. $importchecked . '/>';

			$s .= '<select name="statusnet-import" id="statusnet-import" />';
			$s .= '<option value="0" ' . $importselected[0] . '>' . L10n::t("Disabled") . '</option>';
			$s .= '<option value="1" ' . $importselected[1] . '>' . L10n::t("Full Timeline") . '</option>';
			$s .= '<option value="2" ' . $importselected[2] . '>' . L10n::t("Only Mentions") . '</option>';
			$s .= '</select>';
			$s .= '<div class="clear"></div>';
			/*
			  $s .= '<label id="statusnet-create_user-label" for="statusnet-create_user">'.L10n::t('Automatically create contacts').'</label>';
			  $s .= '<input id="statusnet-create_user" type="checkbox" name="statusnet-create_user" value="1" '. $create_userchecked . '/>';
			  $s .= '<div class="clear"></div>';
			 */
			$s .= '<div id="statusnet-disconnect-wrapper">';
			$s .= '<label id="statusnet-disconnect-label" for="statusnet-disconnect">' . L10n::t('Clear OAuth configuration') . '</label>';
			$s .= '<input id="statusnet-disconnect" type="checkbox" name="statusnet-disconnect" value="1" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
		}
	}
	$s .= '</div><div class="clear"></div>';
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

	if (PConfig::get($post['uid'], 'statusnet', 'import')) {
		// Don't fork if it isn't a reply to a GNU Social post
		if (($post['parent'] != $post['id']) && !Item::exists(['id' => $post['parent'], 'network' => Protocol::STATUSNET])) {
			Logger::log('No GNU Social parent found for item ' . $post['id']);
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

	$statusnet_post = PConfig::get(local_user(), 'statusnet', 'post');
	$statusnet_enable = (($statusnet_post && !empty($_REQUEST['statusnet_enable'])) ? intval($_REQUEST['statusnet_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(PConfig::get(local_user(), 'statusnet', 'post_by_default'))) {
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
	$api = PConfig::get($uid, 'statusnet', 'baseapi');
	$ckey = PConfig::get($uid, 'statusnet', 'consumerkey');
	$csecret = PConfig::get($uid, 'statusnet', 'consumersecret');
	$otoken = PConfig::get($uid, 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($uid, 'statusnet', 'oauthsecret');

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	Logger::log("statusnet_action '" . $action . "' ID: " . $pid, Logger::DATA);

	switch ($action) {
		case "delete":
			$result = $connection->post("statuses/destroy/" . $pid);
			break;
		case "like":
			$result = $connection->post("favorites/create/" . $pid);
			break;
		case "unlike":
			$result = $connection->post("favorites/destroy/" . $pid);
			break;
	}
	Logger::log("statusnet_action '" . $action . "' send, result: " . print_r($result, true), Logger::DEBUG);
}

function statusnet_post_hook(App $a, &$b)
{
	/**
	 * Post to GNU Social
	 */
	if (!PConfig::get($b["uid"], 'statusnet', 'import')) {
		if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
			return;
	}

	$api = PConfig::get($b["uid"], 'statusnet', 'baseapi');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	if ($b['parent'] != $b['id']) {
		Logger::log("statusnet_post_hook: parameter " . print_r($b, true), Logger::DATA);

		// Looking if its a reply to a GNU Social post
		$hostlength = strlen($hostname) + 2;
		if ((substr($b["parent-uri"], 0, $hostlength) != $hostname . "::") && (substr($b["extid"], 0, $hostlength) != $hostname . "::") && (substr($b["thr-parent"], 0, $hostlength) != $hostname . "::")) {
			Logger::log("statusnet_post_hook: no GNU Social post " . $b["parent"]);
			return;
		}

		$condition = ['uri' => $b["thr-parent"], 'uid' => $b["uid"]];
		$orig_post = Item::selectFirst(['author-link', 'uri'], $condition);
		if (!DBA::isResult($orig_post)) {
			Logger::log("statusnet_post_hook: no parent found " . $b["thr-parent"]);
			return;
		} else {
			$iscomment = true;
		}

		$nick = preg_replace("=https?://(.*)/(.*)=ism", "$2", $orig_post["author-link"]);

		$nickname = "@[url=" . $orig_post["author-link"] . "]" . $nick . "[/url]";
		$nicknameplain = "@" . $nick;

		Logger::log("statusnet_post_hook: comparing " . $nickname . " and " . $nicknameplain . " with " . $b["body"], Logger::DEBUG);
		if ((strpos($b["body"], $nickname) === false) && (strpos($b["body"], $nicknameplain) === false)) {
			$b["body"] = $nickname . " " . $b["body"];
		}

		Logger::log("statusnet_post_hook: parent found " . print_r($orig_post, true), Logger::DEBUG);
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

	if (($b['verb'] == ACTIVITY_POST) && $b['deleted']) {
		statusnet_action($a, $b["uid"], substr($orig_post["uri"], $hostlength), "delete");
	}

	if ($b['verb'] == ACTIVITY_LIKE) {
		Logger::log("statusnet_post_hook: parameter 2 " . substr($b["thr-parent"], $hostlength), Logger::DEBUG);
		if ($b['deleted'])
			statusnet_action($a, $b["uid"], substr($b["thr-parent"], $hostlength), "unlike");
		else
			statusnet_action($a, $b["uid"], substr($b["thr-parent"], $hostlength), "like");
		return;
	}

	if ($b['deleted'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	// if posts comes from GNU Social don't send it back
	if ($b['extid'] == Protocol::STATUSNET) {
		return;
	}

	if ($b['app'] == "StatusNet") {
		return;
	}

	Logger::log('GNU Socialpost invoked');

	PConfig::load($b['uid'], 'statusnet');

	$api     = PConfig::get($b['uid'], 'statusnet', 'baseapi');
	$ckey    = PConfig::get($b['uid'], 'statusnet', 'consumerkey');
	$csecret = PConfig::get($b['uid'], 'statusnet', 'consumersecret');
	$otoken  = PConfig::get($b['uid'], 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($b['uid'], 'statusnet', 'oauthsecret');

	if ($ckey && $csecret && $otoken && $osecret) {
		// If it's a repeated message from GNU Social then do a native retweet and exit
		if (statusnet_is_retweet($a, $b['uid'], $b['body'])) {
			return;
		}

		$dent = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);
		$max_char = $dent->get_maxlength(); // max. length for a dent

		PConfig::set($b['uid'], 'statusnet', 'max_char', $max_char);

		$tempfile = "";
		$msgarr = ItemContent::getPlaintextPost($b, $max_char, true, 7);
		$msg = $msgarr["text"];

		if (($msg == "") && isset($msgarr["title"]))
			$msg = Plaintext::shorten($msgarr["title"], $max_char - 50);

		$image = "";

		if (isset($msgarr["url"]) && ($msgarr["type"] != "photo")) {
			$msg .= " \n" . $msgarr["url"];
		} elseif (isset($msgarr["image"]) && ($msgarr["type"] != "video")) {
			$image = $msgarr["image"];
		}

		if ($image != "") {
			$img_str = Network::fetchUrl($image);
			$tempfile = tempnam(get_temppath(), "cache");
			file_put_contents($tempfile, $img_str);
			$postdata = ["status" => $msg, "media[]" => $tempfile];
		} else {
			$postdata = ["status" => $msg];
		}

		// and now send it :-)
		if (strlen($msg)) {
			if ($iscomment) {
				$postdata["in_reply_to_status_id"] = substr($orig_post["uri"], $hostlength);
				Logger::log('statusnet_post send reply ' . print_r($postdata, true), Logger::DEBUG);
			}

			// New code that is able to post pictures
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'codebirdsn.php';
			$cb = CodebirdSN::getInstance();
			$cb->setAPIEndpoint($api);
			$cb->setConsumerKey($ckey, $csecret);
			$cb->setToken($otoken, $osecret);
			$result = $cb->statuses_update($postdata);
			//$result = $dent->post('statuses/update', $postdata);
			Logger::log('statusnet_post send, result: ' . print_r($result, true) .
				"\nmessage: " . $msg . "\nOriginal post: " . print_r($b, true) . "\nPost Data: " . print_r($postdata, true), Logger::DEBUG);

			if (!empty($result->source)) {
				PConfig::set($b["uid"], "statusnet", "application_name", strip_tags($result->source));
			}

			if (!empty($result->error)) {
				Logger::log('Send to GNU Social failed: "' . $result->error . '"');
			} elseif ($iscomment) {
				Logger::log('statusnet_post: Update extid ' . $result->id . " for post id " . $b['id']);
				Item::update(['extid' => $hostname . "::" . $result->id, 'body' => $result->text], ['id' => $b['id']]);
			}
		}
		if ($tempfile != "") {
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
		if ($sitename != "" &&
			$apiurl != "" &&
			$secret != "" &&
			$key != "" &&
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

	$sites = Config::set('statusnet', 'sites', $sites);
}

function statusnet_addon_admin(App $a, &$o)
{
	$sites = Config::get('statusnet', 'sites');
	$sitesform = [];
	if (is_array($sites)) {
		foreach ($sites as $id => $s) {
			$sitesform[] = [
				'sitename' => ["sitename[$id]", "Site name", $s['sitename'], ""],
				'apiurl' => ["apiurl[$id]", "Api url", $s['apiurl'], L10n::t("Base API Path \x28remember the trailing /\x29")],
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
		'sitename' => ["sitename[$id]", L10n::t("Site name"), "", ""],
		'apiurl' => ["apiurl[$id]", "Api url", "", L10n::t("Base API Path \x28remember the trailing /\x29")],
		'secret' => ["secret[$id]", L10n::t("Consumer Secret"), "", ""],
		'key' => ["key[$id]", L10n::t("Consumer Key"), "", ""],
		//'applicationname' => Array("applicationname[$id]", L10n::t("Application name"), "", ""),
	];

	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/statusnet/");
	$o = Renderer::replaceMacros($t, [
		'$submit' => L10n::t('Save Settings'),
		'$sites' => $sitesform,
	]);
}

function statusnet_prepare_body(App $a, &$b)
{
	if ($b["item"]["network"] != Protocol::STATUSNET) {
		return;
	}

	if ($b["preview"]) {
		$max_char = PConfig::get(local_user(), 'statusnet', 'max_char');
		if (intval($max_char) == 0) {
			$max_char = 140;
		}

		$item = $b["item"];
		$item["plink"] = $a->getBaseURL() . "/display/" . $a->user["nickname"] . "/" . $item["parent"];

		$condition = ['uri' => $item["thr-parent"], 'uid' => local_user()];
		$orig_post = Item::selectFirst(['author-link', 'uri'], $condition);
		if (DBA::isResult($orig_post)) {
			$nick = preg_replace("=https?://(.*)/(.*)=ism", "$2", $orig_post["author-link"]);

			$nickname = "@[url=" . $orig_post["author-link"] . "]" . $nick . "[/url]";
			$nicknameplain = "@" . $nick;

			if ((strpos($item["body"], $nickname) === false) && (strpos($item["body"], $nicknameplain) === false)) {
				$item["body"] = $nickname . " " . $item["body"];
			}
		}

		$msgarr = ItemContent::getPlaintextPost($item, $max_char, true, 7);
		$msg = $msgarr["text"];

		if (isset($msgarr["url"]) && ($msgarr["type"] != "photo")) {
			$msg .= " " . $msgarr["url"];
		}

		if (isset($msgarr["image"])) {
			$msg .= " " . $msgarr["image"];
		}

		$b['html'] = nl2br(htmlspecialchars($msg));
	}
}

function statusnet_cron(App $a, $b)
{
	$last = Config::get('statusnet', 'last_poll');

	$poll_interval = intval(Config::get('statusnet', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = STATUSNET_DEFAULT_POLL_INTERVAL;
	}

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			Logger::log('statusnet: poll intervall not reached');
			return;
		}
	}
	Logger::log('statusnet: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'statusnet' AND `k` = 'mirror_posts' AND `v` = '1' ORDER BY RAND() ");
	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			Logger::log('statusnet: fetching for user ' . $rr['uid']);
			statusnet_fetchtimeline($a, $rr['uid']);
		}
	}

	$abandon_days = intval(Config::get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'statusnet' AND `k` = 'import' AND `v` ORDER BY RAND()");
	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			if ($abandon_days != 0) {
				$user = q("SELECT `login_date` FROM `user` WHERE uid=%d AND `login_date` >= '%s'", $rr['uid'], $abandon_limit);
				if (!DBA::isResult($user)) {
					Logger::log('abandoned account: timeline from user ' . $rr['uid'] . ' will not be imported');
					continue;
				}
			}

			Logger::log('statusnet: importing timeline from user ' . $rr['uid']);
			statusnet_fetchhometimeline($a, $rr["uid"], $rr["v"]);
		}
	}

	Logger::log('statusnet: cron_end');

	Config::set('statusnet', 'last_poll', time());
}

function statusnet_fetchtimeline(App $a, $uid)
{
	$ckey    = PConfig::get($uid, 'statusnet', 'consumerkey');
	$csecret = PConfig::get($uid, 'statusnet', 'consumersecret');
	$api     = PConfig::get($uid, 'statusnet', 'baseapi');
	$otoken  = PConfig::get($uid, 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($uid, 'statusnet', 'oauthsecret');
	$lastid  = PConfig::get($uid, 'statusnet', 'lastid');

	require_once 'mod/item.php';
	//  get the application name for the SN app
	//  1st try personal config, then system config and fallback to the
	//  hostname of the node if neither one is set.
	$application_name = PConfig::get($uid, 'statusnet', 'application_name');
	if ($application_name == "") {
		$application_name = Config::get('statusnet', 'application_name');
	}
	if ($application_name == "") {
		$application_name = $a->getHostName();
	}

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$parameters = ["exclude_replies" => true, "trim_user" => true, "contributor_details" => false, "include_rts" => false];

	$first_time = ($lastid == "");

	if ($lastid <> "") {
		$parameters["since_id"] = $lastid;
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

			if ($post->source == "activity") {
				continue;
			}

			if (!empty($post->retweeted_status)) {
				continue;
			}

			if ($post->in_reply_to_status_id != "") {
				continue;
			}

			if (!stristr($post->source, $application_name)) {
				$_SESSION["authenticated"] = true;
				$_SESSION["uid"] = $uid;

				unset($_REQUEST);
				$_REQUEST["api_source"] = true;
				$_REQUEST["profile_uid"] = $uid;
				//$_REQUEST["source"] = "StatusNet";
				$_REQUEST["source"] = $post->source;
				$_REQUEST["extid"] = Protocol::STATUSNET;

				if (isset($post->id)) {
					$_REQUEST['message_id'] = Item::newURI($uid, Protocol::STATUSNET . ":" . $post->id);
				}

				//$_REQUEST["date"] = $post->created_at;

				$_REQUEST["title"] = "";

				$_REQUEST["body"] = add_page_info_to_body($post->text, true);
				if (is_string($post->place->name)) {
					$_REQUEST["location"] = $post->place->name;
				}

				if (is_string($post->place->full_name)) {
					$_REQUEST["location"] = $post->place->full_name;
				}

				if (is_array($post->geo->coordinates)) {
					$_REQUEST["coord"] = $post->geo->coordinates[0] . " " . $post->geo->coordinates[1];
				}

				if (is_array($post->coordinates->coordinates)) {
					$_REQUEST["coord"] = $post->coordinates->coordinates[1] . " " . $post->coordinates->coordinates[0];
				}

				//print_r($_REQUEST);
				if ($_REQUEST["body"] != "") {
					Logger::log('statusnet: posting for user ' . $uid);

					item_post($a);
				}
			}
		}
	}
	PConfig::set($uid, 'statusnet', 'lastid', $lastid);
}

function statusnet_address($contact)
{
	$hostname = Strings::normaliseLink($contact->statusnet_profile_url);
	$nickname = $contact->screen_name;

	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $contact->statusnet_profile_url);

	$address = $contact->screen_name . "@" . $hostname;

	return $address;
}

function statusnet_fetch_contact($uid, $contact, $create_user)
{
	if (empty($contact->statusnet_profile_url)) {
		return -1;
	}

	GContact::update(["url" => $contact->statusnet_profile_url,
		"network" => Protocol::STATUSNET, "photo" => $contact->profile_image_url,
		"name" => $contact->name, "nick" => $contact->screen_name,
		"location" => $contact->location, "about" => $contact->description,
		"addr" => statusnet_address($contact), "generation" => 3]);

	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' AND `network` = '%s'LIMIT 1", intval($uid), DBA::escape(Strings::normaliseLink($contact->statusnet_profile_url)), DBA::escape(Protocol::STATUSNET));

	if (!DBA::isResult($r) && !$create_user) {
		return 0;
	}

	if (DBA::isResult($r) && ($r[0]["readonly"] || $r[0]["blocked"])) {
		Logger::log("statusnet_fetch_contact: Contact '" . $r[0]["nick"] . "' is blocked or readonly.", Logger::DEBUG);
		return -1;
	}

	if (!DBA::isResult($r)) {
		// create contact record
		q("INSERT INTO `contact` ( `uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
					`name`, `nick`, `photo`, `network`, `rel`, `priority`,
					`location`, `about`, `writable`, `blocked`, `readonly`, `pending` )
					VALUES ( %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, '%s', '%s', %d, 0, 0, 0 ) ",
			intval($uid),
			DBA::escape(DateTimeFormat::utcNow()),
			DBA::escape($contact->statusnet_profile_url),
			DBA::escape(Strings::normaliseLink($contact->statusnet_profile_url)),
			DBA::escape(statusnet_address($contact)),
			DBA::escape(Strings::normaliseLink($contact->statusnet_profile_url)),
			DBA::escape(''),
			DBA::escape(''),
			DBA::escape($contact->name),
			DBA::escape($contact->screen_name),
			DBA::escape($contact->profile_image_url),
			DBA::escape(Protocol::STATUSNET),
			intval(Contact::FRIEND),
			intval(1),
			DBA::escape($contact->location),
			DBA::escape($contact->description),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d AND `network` = '%s' LIMIT 1",
			DBA::escape($contact->statusnet_profile_url),
			intval($uid),
			DBA::escape(Protocol::STATUSNET));

		if (!DBA::isResult($r)) {
			return false;
		}

		$contact_id = $r[0]['id'];

		Group::addMember(User::getDefaultGroup($uid), $contact_id);

		$photos = Photo::importProfilePhoto($contact->profile_image_url, $uid, $contact_id);

		q("UPDATE `contact` SET `photo` = '%s',
					`thumb` = '%s',
					`micro` = '%s',
					`avatar-date` = '%s'
				WHERE `id` = %d",
			DBA::escape($photos[0]),
			DBA::escape($photos[1]),
			DBA::escape($photos[2]),
			DBA::escape(DateTimeFormat::utcNow()),
			intval($contact_id)
		);
	} else {
		// update profile photos once every two weeks as we have no notification of when they change.
		//$update_photo = (($r[0]['avatar-date'] < DateTimeFormat::convert('now -2 days', '', '', )) ? true : false);
		$update_photo = ($r[0]['avatar-date'] < DateTimeFormat::utc('now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion
		if ((!$r[0]['photo']) || (!$r[0]['thumb']) || (!$r[0]['micro']) || ($update_photo)) {
			Logger::log("statusnet_fetch_contact: Updating contact " . $contact->screen_name, Logger::DEBUG);

			$photos = Photo::importProfilePhoto($contact->profile_image_url, $uid, $r[0]['id']);

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
						`nick` = '%s',
						`location` = '%s',
						`about` = '%s'
					WHERE `id` = %d",
				DBA::escape($photos[0]),
				DBA::escape($photos[1]),
				DBA::escape($photos[2]),
				DBA::escape(DateTimeFormat::utcNow()),
				DBA::escape(DateTimeFormat::utcNow()),
				DBA::escape(DateTimeFormat::utcNow()),
				DBA::escape($contact->statusnet_profile_url),
				DBA::escape(Strings::normaliseLink($contact->statusnet_profile_url)),
				DBA::escape(statusnet_address($contact)),
				DBA::escape($contact->name),
				DBA::escape($contact->screen_name),
				DBA::escape($contact->location),
				DBA::escape($contact->description),
				intval($r[0]['id'])
			);
		}
	}

	return $r[0]["id"];
}

function statusnet_fetchuser(App $a, $uid, $screen_name = "", $user_id = "")
{
	$ckey    = PConfig::get($uid, 'statusnet', 'consumerkey');
	$csecret = PConfig::get($uid, 'statusnet', 'consumersecret');
	$api     = PConfig::get($uid, 'statusnet', 'baseapi');
	$otoken  = PConfig::get($uid, 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($uid, 'statusnet', 'oauthsecret');

	require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'codebirdsn.php';

	$cb = CodebirdSN::getInstance();
	$cb->setConsumerKey($ckey, $csecret);
	$cb->setToken($otoken, $osecret);

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if (DBA::isResult($r)) {
		$self = $r[0];
	} else {
		return;
	}

	$parameters = [];

	if ($screen_name != "") {
		$parameters["screen_name"] = $screen_name;
	}

	if ($user_id != "") {
		$parameters["user_id"] = $user_id;
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
	Logger::log("statusnet_createpost: start", Logger::DEBUG);

	$api = PConfig::get($uid, 'statusnet', 'baseapi');
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

	$postarray['uri'] = $hostname . "::" . $content->id;

	if (Item::exists(['extid' => $postarray['uri'], 'uid' => $uid])) {
		return [];
	}

	$contactid = 0;

	if (!empty($content->in_reply_to_status_id)) {

		$parent = $hostname . "::" . $content->in_reply_to_status_id;

		$fields = ['uri', 'parent-uri', 'parent'];
		$item = Item::selectFirst($fields, ['uri' => $parent, 'uid' => $uid]);

		if (!DBA::isResult($item)) {
			$item = Item::selectFirst($fields, ['extid' => $parent, 'uid' => $uid]);
		}

		if (DBA::isResult($item)) {
			$postarray['thr-parent'] = $item['uri'];
			$postarray['parent-uri'] = $item['parent-uri'];
			$postarray['parent'] = $item['parent'];
			$postarray['object-type'] = ACTIVITY_OBJ_COMMENT;
		} else {
			$postarray['thr-parent'] = $postarray['uri'];
			$postarray['parent-uri'] = $postarray['uri'];
			$postarray['object-type'] = ACTIVITY_OBJ_NOTE;
		}

		// Is it me?
		$own_url = PConfig::get($uid, 'statusnet', 'own_url');

		if ($content->user->id == $own_url) {
			$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
				intval($uid));

			if (DBA::isResult($r)) {
				$contactid = $r[0]["id"];

				$postarray['owner-name'] = $r[0]["name"];
				$postarray['owner-link'] = $r[0]["url"];
				$postarray['owner-avatar'] = $r[0]["photo"];
			} else {
				return [];
			}
		}
		// Don't create accounts of people who just comment something
		$create_user = false;
	} else {
		$postarray['parent-uri'] = $postarray['uri'];
		$postarray['object-type'] = ACTIVITY_OBJ_NOTE;
	}

	if ($contactid == 0) {
		$contactid = statusnet_fetch_contact($uid, $post->user, $create_user);
		$postarray['owner-name'] = $post->user->name;
		$postarray['owner-link'] = $post->user->statusnet_profile_url;
		$postarray['owner-avatar'] = $post->user->profile_image_url;
	}
	if (($contactid == 0) && !$only_existing_contact) {
		$contactid = $self['id'];
	} elseif ($contactid <= 0) {
		return [];
	}

	$postarray['contact-id'] = $contactid;

	$postarray['verb'] = ACTIVITY_POST;

	$postarray['author-name'] = $content->user->name;
	$postarray['author-link'] = $content->user->statusnet_profile_url;
	$postarray['author-avatar'] = $content->user->profile_image_url;

	// To-Do: Maybe unreliable? Can the api be entered without trailing "/"?
	$hostname = str_replace("/api/", "/notice/", PConfig::get($uid, 'statusnet', 'baseapi'));

	$postarray['plink'] = $hostname . $content->id;
	$postarray['app'] = strip_tags($content->source);

	if ($content->user->protected) {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self['id'] . '>';
	}

	$postarray['body'] = HTML::toBBCode($content->statusnet_html);

	$converted = statusnet_convertmsg($a, $postarray['body'], false);
	$postarray['body'] = $converted["body"];
	$postarray['tag'] = $converted["tags"];

	$postarray['created'] = DateTimeFormat::utc($content->created_at);
	$postarray['edited'] = DateTimeFormat::utc($content->created_at);

	if (!empty($content->place->name)) {
		$postarray["location"] = $content->place->name;
	}

	if (!empty($content->place->full_name)) {
		$postarray["location"] = $content->place->full_name;
	}

	if (!empty($content->geo->coordinates)) {
		$postarray["coord"] = $content->geo->coordinates[0] . " " . $content->geo->coordinates[1];
	}

	if (!empty($content->coordinates->coordinates)) {
		$postarray["coord"] = $content->coordinates->coordinates[1] . " " . $content->coordinates->coordinates[0];
	}

	Logger::log("statusnet_createpost: end", Logger::DEBUG);

	return $postarray;
}

function statusnet_fetchhometimeline(App $a, $uid, $mode = 1)
{
	$conversations = [];

	$ckey    = PConfig::get($uid, 'statusnet', 'consumerkey');
	$csecret = PConfig::get($uid, 'statusnet', 'consumersecret');
	$api     = PConfig::get($uid, 'statusnet', 'baseapi');
	$otoken  = PConfig::get($uid, 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($uid, 'statusnet', 'oauthsecret');
	$create_user = PConfig::get($uid, 'statusnet', 'create_user');

	// "create_user" is deactivated, since currently you cannot add users manually by now
	$create_user = true;

	Logger::log("statusnet_fetchhometimeline: Fetching for user " . $uid, Logger::DEBUG);

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$own_contact = statusnet_fetch_own_contact($a, $uid);

	if (empty($own_contact)) {
		return;
	}

	$r = q("SELECT * FROM `contact` WHERE `id` = %d AND `uid` = %d LIMIT 1",
		intval($own_contact),
		intval($uid));

	if (DBA::isResult($r)) {
		$nick = $r[0]["nick"];
	} else {
		Logger::log("statusnet_fetchhometimeline: Own GNU Social contact not found for user " . $uid, Logger::DEBUG);
		return;
	}

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if (DBA::isResult($r)) {
		$self = $r[0];
	} else {
		Logger::log("statusnet_fetchhometimeline: Own contact not found for user " . $uid, Logger::DEBUG);
		return;
	}

	$u = q("SELECT * FROM user WHERE uid = %d LIMIT 1",
		intval($uid));
	if (!DBA::isResult($u)) {
		Logger::log("statusnet_fetchhometimeline: Own user not found for user " . $uid, Logger::DEBUG);
		return;
	}

	$parameters = ["exclude_replies" => false, "trim_user" => false, "contributor_details" => true, "include_rts" => true];
	//$parameters["count"] = 200;

	if ($mode == 1) {
		// Fetching timeline
		$lastid = PConfig::get($uid, 'statusnet', 'lasthometimelineid');
		//$lastid = 1;

		$first_time = ($lastid == "");

		if ($lastid != "") {
			$parameters["since_id"] = $lastid;
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
				$errormsg = "Unknown error";
			}

			Logger::log("statusnet_fetchhometimeline: Error fetching home timeline: " . $errormsg, Logger::DEBUG);
			return;
		}

		$posts = array_reverse($items);

		Logger::log("statusnet_fetchhometimeline: Fetching timeline for user " . $uid . " " . sizeof($posts) . " items", Logger::DEBUG);

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

					if (trim($postarray['body']) == "") {
						continue;
					}

					$item = Item::insert($postarray);
					$postarray["id"] = $item;

					Logger::log('statusnet_fetchhometimeline: User ' . $self["nick"] . ' posted home timeline item ' . $item);
				}
			}
		}
		PConfig::set($uid, 'statusnet', 'lasthometimelineid', $lastid);
	}

	// Fetching mentions
	$lastid = PConfig::get($uid, 'statusnet', 'lastmentionid');
	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	$items = $connection->get('statuses/mentions_timeline', $parameters);

	if (!is_array($items)) {
		Logger::log("statusnet_fetchhometimeline: Error fetching mentions: " . print_r($items, true), Logger::DEBUG);
		return;
	}

	$posts = array_reverse($items);

	Logger::log("statusnet_fetchhometimeline: Fetching mentions for user " . $uid . " " . sizeof($posts) . " items", Logger::DEBUG);

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
				if (trim($postarray['body']) == "") {
					continue;
				}

				$item = Item::insert($postarray);

				Logger::log('statusnet_fetchhometimeline: User ' . $self["nick"] . ' posted mention timeline item ' . $item);
			}
		}
	}

	PConfig::set($uid, 'statusnet', 'lastmentionid', $lastid);
}

function statusnet_complete_conversation(App $a, $uid, $self, $create_user, $nick, $conversation)
{
	$ckey    = PConfig::get($uid, 'statusnet', 'consumerkey');
	$csecret = PConfig::get($uid, 'statusnet', 'consumersecret');
	$api     = PConfig::get($uid, 'statusnet', 'baseapi');
	$otoken  = PConfig::get($uid, 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($uid, 'statusnet', 'oauthsecret');
	$own_url = PConfig::get($uid, 'statusnet', 'own_url');

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$parameters["count"] = 200;

	$items = $connection->get('statusnet/conversation/' . $conversation, $parameters);
	if (is_array($items)) {
		$posts = array_reverse($items);

		foreach ($posts as $post) {
			$postarray = statusnet_createpost($a, $uid, $post, $self, false, false);

			if (empty($postarray['body'])) {
				continue;
			}

			$item = Item::insert($postarray);
			$postarray["id"] = $item;

			Logger::log('statusnet_complete_conversation: User ' . $self["nick"] . ' posted home timeline item ' . $item);
		}
	}
}

function statusnet_convertmsg(App $a, $body, $no_tags = false)
{
	$body = preg_replace("=\[url\=https?://([0-9]*).([0-9]*).([0-9]*).([0-9]*)/([0-9]*)\](.*?)\[\/url\]=ism", "$1.$2.$3.$4/$5", $body);

	$URLSearchString = "^\[\]";
	$links = preg_match_all("/[^!#@]\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER);

	$footer = "";
	$footerurl = "";
	$footerlink = "";
	$type = "";

	if ($links) {
		foreach ($matches AS $match) {
			$search = "[url=" . $match[1] . "]" . $match[2] . "[/url]";

			Logger::log("statusnet_convertmsg: expanding url " . $match[1], Logger::DEBUG);

			$expanded_url = Network::finalUrl($match[1]);

			Logger::log("statusnet_convertmsg: fetching data for " . $expanded_url, Logger::DEBUG);

			$oembed_data = OEmbed::fetchURL($expanded_url, true);

			Logger::log("statusnet_convertmsg: fetching data: done", Logger::DEBUG);

			if ($type == "") {
				$type = $oembed_data->type;
			}

			if ($oembed_data->type == "video") {
				//$body = str_replace($search, "[video]".$expanded_url."[/video]", $body);
				$type = $oembed_data->type;
				$footerurl = $expanded_url;
				$footerlink = "[url=" . $expanded_url . "]" . $expanded_url . "[/url]";

				$body = str_replace($search, $footerlink, $body);
			} elseif (($oembed_data->type == "photo") && isset($oembed_data->url)) {
				$body = str_replace($search, "[url=" . $expanded_url . "][img]" . $oembed_data->url . "[/img][/url]", $body);
			} elseif ($oembed_data->type != "link") {
				$body = str_replace($search, "[url=" . $expanded_url . "]" . $expanded_url . "[/url]", $body);
			} else {
				$img_str = Network::fetchUrl($expanded_url, true, $redirects, 4);

				$tempfile = tempnam(get_temppath(), "cache");
				file_put_contents($tempfile, $img_str);
				$mime = mime_content_type($tempfile);
				unlink($tempfile);

				if (substr($mime, 0, 6) == "image/") {
					$type = "photo";
					$body = str_replace($search, "[img]" . $expanded_url . "[/img]", $body);
				} else {
					$type = $oembed_data->type;
					$footerurl = $expanded_url;
					$footerlink = "[url=" . $expanded_url . "]" . $expanded_url . "[/url]";

					$body = str_replace($search, $footerlink, $body);
				}
			}
		}

		if ($footerurl != "") {
			$footer = add_page_info($footerurl);
		}

		if (($footerlink != "") && (trim($footer) != "")) {
			$removedlink = trim(str_replace($footerlink, "", $body));

			if (($removedlink == "") || strstr($body, $removedlink)) {
				$body = $removedlink;
			}

			$body .= $footer;
		}
	}

	if ($no_tags) {
		return ["body" => $body, "tags" => ""];
	}

	$str_tags = '';

	$cnt = preg_match_all("/([!#@])\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER);
	if ($cnt) {
		foreach ($matches as $mtch) {
			if (strlen($str_tags)) {
				$str_tags .= ',';
			}

			if ($mtch[1] == "#") {
				// Replacing the hash tags that are directed to the GNU Social server with internal links
				$snhash = "#[url=" . $mtch[2] . "]" . $mtch[3] . "[/url]";
				$frdchash = '#[url=' . $a->getBaseURL() . '/search?tag=' . $mtch[3] . ']' . $mtch[3] . '[/url]';
				$body = str_replace($snhash, $frdchash, $body);

				$str_tags .= $frdchash;
			} else {
				$str_tags .= "@[url=" . $mtch[2] . "]" . $mtch[3] . "[/url]";
			}
			// To-Do:
			// There is a problem with links with to GNU Social groups, so these links are stored with "@" like friendica groups
			//$str_tags .= $mtch[1]."[url=".$mtch[2]."]".$mtch[3]."[/url]";
		}
	}

	return ["body" => $body, "tags" => $str_tags];
}

function statusnet_fetch_own_contact(App $a, $uid)
{
	$ckey    = PConfig::get($uid, 'statusnet', 'consumerkey');
	$csecret = PConfig::get($uid, 'statusnet', 'consumersecret');
	$api     = PConfig::get($uid, 'statusnet', 'baseapi');
	$otoken  = PConfig::get($uid, 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($uid, 'statusnet', 'oauthsecret');
	$own_url = PConfig::get($uid, 'statusnet', 'own_url');

	$contact_id = 0;

	if ($own_url == "") {
		$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

		// Fetching user data
		$user = $connection->get('account/verify_credentials');

		if (empty($user)) {
			return false;
		}

		PConfig::set($uid, 'statusnet', 'own_url', Strings::normaliseLink($user->statusnet_profile_url));

		$contact_id = statusnet_fetch_contact($uid, $user, true);
	} else {
		$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
			intval($uid), DBA::escape($own_url));
		if (DBA::isResult($r)) {
			$contact_id = $r[0]["id"];
		} else {
			PConfig::delete($uid, 'statusnet', 'own_url');
		}
	}
	return $contact_id;
}

function statusnet_is_retweet(App $a, $uid, $body)
{
	$body = trim($body);

	// Skip if it isn't a pure repeated messages
	// Does it start with a share?
	if (strpos($body, "[share") > 0) {
		return false;
	}

	// Does it end with a share?
	if (strlen($body) > (strrpos($body, "[/share]") + 8)) {
		return false;
	}

	$attributes = preg_replace("/\[share(.*?)\]\s?(.*?)\s?\[\/share\]\s?/ism", "$1", $body);
	// Skip if there is no shared message in there
	if ($body == $attributes) {
		return false;
	}

	$link = "";
	preg_match("/link='(.*?)'/ism", $attributes, $matches);
	if (!empty($matches[1])) {
		$link = $matches[1];
	}

	preg_match('/link="(.*?)"/ism', $attributes, $matches);
	if (!empty($matches[1])) {
		$link = $matches[1];
	}

	$ckey    = PConfig::get($uid, 'statusnet', 'consumerkey');
	$csecret = PConfig::get($uid, 'statusnet', 'consumersecret');
	$api     = PConfig::get($uid, 'statusnet', 'baseapi');
	$otoken  = PConfig::get($uid, 'statusnet', 'oauthtoken');
	$osecret = PConfig::get($uid, 'statusnet', 'oauthsecret');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	$id = preg_replace("=https?://" . $hostname . "/notice/(.*)=ism", "$1", $link);

	if ($id == $link) {
		return false;
	}

	Logger::log('statusnet_is_retweet: Retweeting id ' . $id . ' for user ' . $uid, Logger::DEBUG);

	$connection = new StatusNetOAuth($api, $ckey, $csecret, $otoken, $osecret);

	$result = $connection->post('statuses/retweet/' . $id);

	Logger::log('statusnet_is_retweet: result ' . print_r($result, true), Logger::DEBUG);

	return isset($result->id);
}
