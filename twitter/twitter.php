<?php
/**
 * Name: Twitter Connector
 * Description: Bidirectional (posting, relaying and reading) connector for Twitter.
 * Version: 1.1.0
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 * Maintainer: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 *
 * Copyright (c) 2011-2013 Tobias Diekershoff, Michael Vogel, Hypolite Petovan
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
/*   Twitter Addon for Friendica
 *
 *   Author: Tobias Diekershoff
 *           tobias.diekershoff@gmx.net
 *
 *   License:3-clause BSD license
 *
 *   Configuration:
 *     To use this addon you need a OAuth Consumer key pair (key & secret)
 *     you can get it from Twitter at https://twitter.com/apps
 *
 *     Register your Friendica site as "Client" application with "Read & Write" access
 *     we do not need "Twitter as login". When you've registered the app you get the
 *     OAuth Consumer key and secret pair for your application/site.
 *
 *     Add this key pair to your global config/addon.config.php or use the admin panel.
 *
 *     	'twitter' => [
 * 		    'consumerkey' => '',
 *  		'consumersecret' => '',
 *      ],
 *
 *     To activate the addon itself add it to the system.addon
 *     setting. After this, your user can configure their Twitter account settings
 *     from "Settings -> Addon Settings".
 *
 *     Requirements: PHP5, curl
 */

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use Codebird\Codebird;
use Friendica\App;
use Friendica\Content\OEmbed;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\Model\Contact;
use Friendica\Model\Conversation;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\ItemContent;
use Friendica\Model\User;
use Friendica\Protocol\Activity;
use Friendica\Registry\App as AppR;
use Friendica\Util\ConfigFileLoader;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Images;
use Friendica\Util\Network;
use Friendica\Util\Strings;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

define('TWITTER_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function twitter_install()
{
	//  we need some hooks, for the configuration and for sending tweets
	Hook::register('load_config'            , __FILE__, 'twitter_load_config');
	Hook::register('connector_settings'     , __FILE__, 'twitter_settings');
	Hook::register('connector_settings_post', __FILE__, 'twitter_settings_post');
	Hook::register('hook_fork'              , __FILE__, 'twitter_hook_fork');
	Hook::register('post_local'             , __FILE__, 'twitter_post_local');
	Hook::register('notifier_normal'        , __FILE__, 'twitter_post_hook');
	Hook::register('jot_networks'           , __FILE__, 'twitter_jot_nets');
	Hook::register('cron'                   , __FILE__, 'twitter_cron');
	Hook::register('follow'                 , __FILE__, 'twitter_follow');
	Hook::register('expire'                 , __FILE__, 'twitter_expire');
	Hook::register('prepare_body'           , __FILE__, 'twitter_prepare_body');
	Hook::register('check_item_notification', __FILE__, 'twitter_check_item_notification');
	Logger::info("installed twitter");
}

function twitter_uninstall()
{
	Hook::unregister('load_config'            , __FILE__, 'twitter_load_config');
	Hook::unregister('connector_settings'     , __FILE__, 'twitter_settings');
	Hook::unregister('connector_settings_post', __FILE__, 'twitter_settings_post');
	Hook::unregister('hook_fork'              , __FILE__, 'twitter_hook_fork');
	Hook::unregister('post_local'             , __FILE__, 'twitter_post_local');
	Hook::unregister('notifier_normal'        , __FILE__, 'twitter_post_hook');
	Hook::unregister('jot_networks'           , __FILE__, 'twitter_jot_nets');
	Hook::unregister('cron'                   , __FILE__, 'twitter_cron');
	Hook::unregister('follow'                 , __FILE__, 'twitter_follow');
	Hook::unregister('expire'                 , __FILE__, 'twitter_expire');
	Hook::unregister('prepare_body'           , __FILE__, 'twitter_prepare_body');
	Hook::unregister('check_item_notification', __FILE__, 'twitter_check_item_notification');

	// old setting - remove only
	Hook::unregister('post_local_end'     , __FILE__, 'twitter_post_hook');
	Hook::unregister('addon_settings'     , __FILE__, 'twitter_settings');
	Hook::unregister('addon_settings_post', __FILE__, 'twitter_settings_post');
}

function twitter_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('twitter'));
}

function twitter_check_item_notification(App $a, array &$notification_data)
{
	$own_id = PConfig::get($notification_data["uid"], 'twitter', 'own_id');

	$own_user = q("SELECT `url` FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
			intval($notification_data["uid"]),
			DBA::escape("twitter::".$own_id)
	);

	if ($own_user) {
		$notification_data["profiles"][] = $own_user[0]["url"];
	}
}

function twitter_follow(App $a, array &$contact)
{
	Logger::info('Check if contact is twitter contact', ['url' => $contact["url"]]);

	if (!strstr($contact["url"], "://twitter.com") && !strstr($contact["url"], "@twitter.com")) {
		return;
	}

	// contact seems to be a twitter contact, so continue
	$nickname = preg_replace("=https?://twitter.com/(.*)=ism", "$1", $contact["url"]);
	$nickname = str_replace("@twitter.com", "", $nickname);

	$uid = $a->user["uid"];

	$ckey = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');

	// If the addon is not configured (general or for this user) quit here
	if (empty($ckey) || empty($csecret) || empty($otoken) || empty($osecret)) {
		$contact = false;
		return;
	}

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
	$connection->post('friendships/create', ['screen_name' => $nickname]);

	twitter_fetchuser($a, $uid, $nickname);

	$r = q("SELECT name,nick,url,addr,batch,notify,poll,request,confirm,poco,photo,priority,network,alias,pubkey
		FROM `contact` WHERE `uid` = %d AND `nick` = '%s'",
				intval($uid),
				DBA::escape($nickname));
	if (DBA::isResult($r)) {
		$contact["contact"] = $r[0];
	}
}

function twitter_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (PConfig::get(local_user(), 'twitter', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'twitter_enable',
				L10n::t('Post to Twitter'),
				PConfig::get(local_user(), 'twitter', 'post_by_default')
			]
		];
	}
}


function twitter_settings_post(App $a)
{
	if (!local_user()) {
		return;
	}
	// don't check twitter settings if twitter submit button is not clicked
	if (empty($_POST['twitter-disconnect']) && empty($_POST['twitter-submit'])) {
		return;
	}

	if (!empty($_POST['twitter-disconnect'])) {
		/*		 * *
		 * if the twitter-disconnect checkbox is set, clear the OAuth key/secret pair
		 * from the user configuration
		 */
		PConfig::delete(local_user(), 'twitter', 'consumerkey');
		PConfig::delete(local_user(), 'twitter', 'consumersecret');
		PConfig::delete(local_user(), 'twitter', 'oauthtoken');
		PConfig::delete(local_user(), 'twitter', 'oauthsecret');
		PConfig::delete(local_user(), 'twitter', 'post');
		PConfig::delete(local_user(), 'twitter', 'post_by_default');
		PConfig::delete(local_user(), 'twitter', 'lastid');
		PConfig::delete(local_user(), 'twitter', 'mirror_posts');
		PConfig::delete(local_user(), 'twitter', 'import');
		PConfig::delete(local_user(), 'twitter', 'create_user');
		PConfig::delete(local_user(), 'twitter', 'own_id');
	} else {
		if (isset($_POST['twitter-pin'])) {
			//  if the user supplied us with a PIN from Twitter, let the magic of OAuth happen
			Logger::notice('got a Twitter PIN');
			$ckey    = Config::get('twitter', 'consumerkey');
			$csecret = Config::get('twitter', 'consumersecret');
			//  the token and secret for which the PIN was generated were hidden in the settings
			//  form as token and token2, we need a new connection to Twitter using these token
			//  and secret to request a Access Token with the PIN
			try {
				if (empty($_POST['twitter-pin'])) {
					throw new Exception(L10n::t('You submitted an empty PIN, please Sign In with Twitter again to get a new one.'));
				}

				$connection = new TwitterOAuth($ckey, $csecret, $_POST['twitter-token'], $_POST['twitter-token2']);
				$token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_POST['twitter-pin']]);
				//  ok, now that we have the Access Token, save them in the user config
				PConfig::set(local_user(), 'twitter', 'oauthtoken', $token['oauth_token']);
				PConfig::set(local_user(), 'twitter', 'oauthsecret', $token['oauth_token_secret']);
				PConfig::set(local_user(), 'twitter', 'post', 1);
			} catch(Exception $e) {
				info($e->getMessage());
			} catch(TwitterOAuthException $e) {
				info($e->getMessage());
			}
			//  reload the Addon Settings page, if we don't do it see Bug #42
			AppR::baseUrl()->redirect('settings/connectors');
		} else {
			//  if no PIN is supplied in the POST variables, the user has changed the setting
			//  to post a tweet for every new __public__ posting to the wall
			PConfig::set(local_user(), 'twitter', 'post', intval($_POST['twitter-enable']));
			PConfig::set(local_user(), 'twitter', 'post_by_default', intval($_POST['twitter-default']));
			PConfig::set(local_user(), 'twitter', 'mirror_posts', intval($_POST['twitter-mirror']));
			PConfig::set(local_user(), 'twitter', 'import', intval($_POST['twitter-import']));
			PConfig::set(local_user(), 'twitter', 'create_user', intval($_POST['twitter-create_user']));

			if (!intval($_POST['twitter-mirror'])) {
				PConfig::delete(local_user(), 'twitter', 'lastid');
			}

			info(L10n::t('Twitter settings updated.') . EOL);
		}
	}
}

function twitter_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}
	AppR::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . AppR::baseUrl()->get() . '/addon/twitter/twitter.css' . '" media="all" />' . "\r\n";
	/*	 * *
	 * 1) Check that we have global consumer key & secret
	 * 2) If no OAuthtoken & stuff is present, generate button to get some
	 * 3) Checkbox for "Send public notices (280 chars only)
	 */
	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get(local_user(), 'twitter', 'oauthtoken');
	$osecret = PConfig::get(local_user(), 'twitter', 'oauthsecret');

	$enabled            = intval(PConfig::get(local_user(), 'twitter', 'post'));
	$defenabled         = intval(PConfig::get(local_user(), 'twitter', 'post_by_default'));
	$mirrorenabled      = intval(PConfig::get(local_user(), 'twitter', 'mirror_posts'));
	$importenabled      = intval(PConfig::get(local_user(), 'twitter', 'import'));
	$create_userenabled = intval(PConfig::get(local_user(), 'twitter', 'create_user'));

	$css = (($enabled) ? '' : '-disabled');

	$s .= '<span id="settings_twitter_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_twitter_expanded\'); openClose(\'settings_twitter_inflated\');">';
	$s .= '<img class="connector' . $css . '" src="images/twitter.png" /><h3 class="connector">' . L10n::t('Twitter Import/Export/Mirror') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_twitter_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_twitter_expanded\'); openClose(\'settings_twitter_inflated\');">';
	$s .= '<img class="connector' . $css . '" src="images/twitter.png" /><h3 class="connector">' . L10n::t('Twitter Import/Export/Mirror') . '</h3>';
	$s .= '</span>';

	if ((!$ckey) && (!$csecret)) {
		/* no global consumer keys
		 * display warning and skip personal config
		 */
		$s .= '<p>' . L10n::t('No consumer key pair for Twitter found. Please contact your site administrator.') . '</p>';
	} else {
		// ok we have a consumer key pair now look into the OAuth stuff
		if ((!$otoken) && (!$osecret)) {
			/* the user has not yet connected the account to twitter...
			 * get a temporary OAuth key/secret pair and display a button with
			 * which the user can request a PIN to connect the account to a
			 * account at Twitter.
			 */
			$connection = new TwitterOAuth($ckey, $csecret);
			try {
				$result = $connection->oauth('oauth/request_token', ['oauth_callback' => 'oob']);
				$s .= '<p>' . L10n::t('At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.') . '</p>';
				$s .= '<a href="' . $connection->url('oauth/authorize', ['oauth_token' => $result['oauth_token']]) . '" target="_twitter"><img src="addon/twitter/lighter.png" alt="' . L10n::t('Log in with Twitter') . '"></a>';
				$s .= '<div id="twitter-pin-wrapper">';
				$s .= '<label id="twitter-pin-label" for="twitter-pin">' . L10n::t('Copy the PIN from Twitter here') . '</label>';
				$s .= '<input id="twitter-pin" type="text" name="twitter-pin" />';
				$s .= '<input id="twitter-token" type="hidden" name="twitter-token" value="' . $result['oauth_token'] . '" />';
				$s .= '<input id="twitter-token2" type="hidden" name="twitter-token2" value="' . $result['oauth_token_secret'] . '" />';
				$s .= '</div><div class="clear"></div>';
				$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
			} catch (TwitterOAuthException $e) {
				$s .= '<p>' . L10n::t('An error occured: ') . $e->getMessage() . '</p>';
			}
		} else {
			/*			 * *
			 *  we have an OAuth key / secret pair for the user
			 *  so let's give a chance to disable the postings to Twitter
			 */
			$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
			try {
				$details = $connection->get('account/verify_credentials');

				$field_checkbox = Renderer::getMarkupTemplate('field_checkbox.tpl');

				if (property_exists($details, 'screen_name') &&
				    property_exists($details, 'description') &&
				    property_exists($details, 'profile_image_url')) {
					$s .= '<div id="twitter-info" >
					<p>' . L10n::t('Currently connected to: ') . '<a href="https://twitter.com/' . $details->screen_name . '" target="_twitter">' . $details->screen_name . '</a>
						<button type="submit" name="twitter-disconnect" value="1">' . L10n::t('Disconnect') . '</button>
					</p>
					<p id="twitter-info-block">
						<a href="https://twitter.com/' . $details->screen_name . '" target="_twitter"><img id="twitter-avatar" src="' . $details->profile_image_url . '" /></a>
						<em>' . $details->description . '</em>
					</p>
				</div>';
				} else {
					$s .= '<div id="twitter-info" >
					<p>Invalid Twitter info</p>
				</div>';
					Logger::info('Invalid twitter info (verify credentials).', ['auth' => TwitterOAuth::class]);
				}
				$s .= '<div class="clear"></div>';

				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-enable', L10n::t('Allow posting to Twitter'), $enabled, L10n::t('If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.')]
				]);
				if ($a->user['hidewall']) {
					$s .= '<p>' . L10n::t('<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.') . '</p>';
				}
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-default', L10n::t('Send public postings to Twitter by default'), $defenabled, '']
				]);
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-mirror', L10n::t('Mirror all posts from twitter that are no replies'), $mirrorenabled, '']
				]);
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-import', L10n::t('Import the remote timeline'), $importenabled, '']
				]);
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-create_user', L10n::t('Automatically create contacts'), $create_userenabled, L10n::t('This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again.')]
				]);
				$s .= '<div class="clear"></div>';
				$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
			} catch (TwitterOAuthException $e) {
				$s .= '<p>' . L10n::t('An error occured: ') . $e->getMessage() . '</p>';
			}
		}
	}
	$s .= '</div><div class="clear"></div>';
}

function twitter_hook_fork(App $a, array &$b)
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

	// if post comes from twitter don't send it back
	if ($post['extid'] == Protocol::TWITTER) {
		$b['execute'] = false;
		return;
	}

	if ($post['app'] == 'Twitter') {
		$b['execute'] = false;
		return;
	}

	if (PConfig::get($post['uid'], 'twitter', 'import')) {
		// Don't fork if it isn't a reply to a twitter post
		if (($post['parent'] != $post['id']) && !Item::exists(['id' => $post['parent'], 'network' => Protocol::TWITTER])) {
			Logger::notice('No twitter parent found', ['item' => $post['id']]);
			$b['execute'] = false;
			return;
		}
	} else {
		// Comments are never exported when we don't import the twitter timeline
		if (!strstr($post['postopts'], 'twitter') || ($post['parent'] != $post['id']) || $post['private']) {
			$b['execute'] = false;
			return;
		}
        }
}

function twitter_post_local(App $a, array &$b)
{
	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	$twitter_post = intval(PConfig::get(local_user(), 'twitter', 'post'));
	$twitter_enable = (($twitter_post && !empty($_REQUEST['twitter_enable'])) ? intval($_REQUEST['twitter_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(PConfig::get(local_user(), 'twitter', 'post_by_default'))) {
		$twitter_enable = 1;
	}

	if (!$twitter_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'twitter';
}

function twitter_action(App $a, $uid, $pid, $action)
{
	$ckey = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

	$post = ['id' => $pid];

	Logger::log("twitter_action '" . $action . "' ID: " . $pid . " data: " . print_r($post, true), Logger::DATA);

	switch ($action) {
		case "delete":
			// To-Do: $result = $connection->post('statuses/destroy', $post);
			$result = [];
			break;
		case "like":
			$result = $connection->post('favorites/create', $post);
			break;
		case "unlike":
			$result = $connection->post('favorites/destroy', $post);
			break;
		default:
			Logger::log('Unhandled action ' . $action, Logger::DEBUG);
			$result = [];
	}
	Logger::log("twitter_action '" . $action . "' send, result: " . print_r($result, true), Logger::DEBUG);
}

function twitter_post_hook(App $a, array &$b)
{
	// Post to Twitter
	if (!PConfig::get($b["uid"], 'twitter', 'import')
		&& ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		Logger::log("twitter_post_hook: parameter " . print_r($b, true), Logger::DATA);

		// Looking if its a reply to a twitter post
		if ((substr($b["parent-uri"], 0, 9) != "twitter::")
			&& (substr($b["extid"], 0, 9) != "twitter::")
			&& (substr($b["thr-parent"], 0, 9) != "twitter::"))
		{
			Logger::log("twitter_post_hook: no twitter post " . $b["parent"]);
			return;
		}

		$condition = ['uri' => $b["thr-parent"], 'uid' => $b["uid"]];
		$orig_post = Item::selectFirst([], $condition);
		if (!DBA::isResult($orig_post)) {
			Logger::log("twitter_post_hook: no parent found " . $b["thr-parent"]);
			return;
		} else {
			$iscomment = true;
		}


		$nicknameplain = preg_replace("=https?://twitter.com/(.*)=ism", "$1", $orig_post["author-link"]);
		$nickname = "@[url=" . $orig_post["author-link"] . "]" . $nicknameplain . "[/url]";
		$nicknameplain = "@" . $nicknameplain;

		Logger::log("twitter_post_hook: comparing " . $nickname . " and " . $nicknameplain . " with " . $b["body"], Logger::DEBUG);
		if ((strpos($b["body"], $nickname) === false) && (strpos($b["body"], $nicknameplain) === false)) {
			$b["body"] = $nickname . " " . $b["body"];
		}

		Logger::log("twitter_post_hook: parent found " . print_r($orig_post, true), Logger::DATA);
	} else {
		$iscomment = false;

		if ($b['private'] || !strstr($b['postopts'], 'twitter')) {
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
		twitter_action($a, $b["uid"], substr($orig_post["uri"], 9), "delete");
	}

	if ($b['verb'] == Activity::LIKE) {
		Logger::log("twitter_post_hook: parameter 2 " . substr($b["thr-parent"], 9), Logger::DEBUG);
		if ($b['deleted']) {
			twitter_action($a, $b["uid"], substr($b["thr-parent"], 9), "unlike");
		} else {
			twitter_action($a, $b["uid"], substr($b["thr-parent"], 9), "like");
		}

		return;
	}

	if ($b['deleted'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	// if post comes from twitter don't send it back
	if ($b['extid'] == Protocol::TWITTER) {
		return;
	}

	if ($b['app'] == "Twitter") {
		return;
	}

	Logger::notice('twitter post invoked', ['id' => $b['id'], 'guid' => $b['guid']]);

	PConfig::load($b['uid'], 'twitter');

	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get($b['uid'], 'twitter', 'oauthtoken');
	$osecret = PConfig::get($b['uid'], 'twitter', 'oauthsecret');

	if ($ckey && $csecret && $otoken && $osecret) {
		Logger::log('twitter: we have customer key and oauth stuff, going to send.', Logger::DEBUG);

		// If it's a repeated message from twitter then do a native retweet and exit
		if (twitter_is_retweet($a, $b['uid'], $b['body'])) {
			return;
		}

		Codebird::setConsumerKey($ckey, $csecret);
		$cb = Codebird::getInstance();
		$cb->setToken($otoken, $osecret);

		$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

		// Set the timeout for upload to 30 seconds
		$connection->setTimeouts(10, 30);

		$max_char = 280;

		// Handling non-native reshares
		$b['body'] = Friendica\Content\Text\BBCode::convertShare(
			$b['body'],
			function (array $attributes, array $author_contact, $content, $is_quote_share) {
				return twitter_convert_share($attributes, $author_contact, $content, $is_quote_share);
			}
		);

		$b['body'] = twitter_update_mentions($b['body']);

		$msgarr = ItemContent::getPlaintextPost($b, $max_char, true, 8);
		Logger::info('Got plaintext', ['id' => $b['id'], 'message' => $msgarr]);
		$msg = $msgarr["text"];

		if (($msg == "") && isset($msgarr["title"])) {
			$msg = Plaintext::shorten($msgarr["title"], $max_char - 50);
		}

		if (!empty($msgarr['url']) && ($msgarr['url'] == $b['plink']) && !empty($msgarr['images']) && (count($msgarr['images']) <= 4)) {
			$url_added = false;
		} elseif (isset($msgarr["url"]) && ($msgarr["type"] != "photo")) {
			$msg .= "\n" . $msgarr["url"];
			$url_added = true;
		} else {
			$url_added = false;
		}

		if (empty($msg)) {
			Logger::info('Empty message', ['id' => $b['id']]);
			return;
		}

		// and now tweet it :-)
		$post = [];

		if (!empty($msgarr['images'])) {
			Logger::info('Got images', ['id' => $b['id'], 'images' => $msgarr['images']]);
			try {
				$media_ids = [];
				foreach ($msgarr['images'] as $image) {
					if (count($media_ids) == 4) {
						continue;
					}

					$img_str = Network::fetchUrl($image['url']);

					$tempfile = tempnam(get_temppath(), 'cache');
					file_put_contents($tempfile, $img_str);

					Logger::info('Uploading', ['id' => $b['id'], 'image' => $image['url']]);
					$media = $connection->upload('media/upload', ['media' => $tempfile]);

					unlink($tempfile);

					if (isset($media->media_id_string)) {
						$media_ids[] = $media->media_id_string;

						if (!empty($image['description'])) {
							$data = ['media_id' => $media->media_id_string,
								'alt_text' => ['text' => substr($image['description'], 0, 420)]];
							$ret = $cb->media_metadata_create($data);
							Logger::info('Metadata create', ['id' => $b['id'], 'data' => $data, 'return' => json_encode($ret)]);
						}
					} else {
						throw new Exception('Failed upload', ['id' => $b['id'], 'image' => $image['url']]);
					}
				}
				$post['media_ids'] = implode(',', $media_ids);
				if (empty($post['media_ids'])) {
					unset($post['media_ids']);
				}
			} catch (Exception $e) {
				Logger::info('Exception when trying to send to Twitter', ['id' => $b['id'], 'message' => $e->getMessage()]);
			}
		}

		$post['status'] = $msg;

		if ($iscomment) {
			$post["in_reply_to_status_id"] = substr($orig_post["uri"], 9);
		}

		$url = 'statuses/update';
		$result = $connection->post($url, $post);
		Logger::info('twitter_post send', ['id' => $b['id'], 'result' => $result]);

		if (!empty($result->source)) {
			Config::set("twitter", "application_name", strip_tags($result->source));
		}

		if (!empty($result->errors)) {
			Logger::info('Send to Twitter failed', ['id' => $b['id'], 'error' => $result->errors]);
			Worker::defer();
		} elseif ($iscomment) {
			Logger::info('Update extid', ['id' => $b['id'], 'extid' => $result->id_str]);
			Item::update(['extid' => "twitter::" . $result->id_str], ['id' => $b['id']]);
		}
	}
}

function twitter_addon_admin_post(App $a)
{
	$consumerkey    = !empty($_POST['consumerkey'])    ? Strings::escapeTags(trim($_POST['consumerkey']))    : '';
	$consumersecret = !empty($_POST['consumersecret']) ? Strings::escapeTags(trim($_POST['consumersecret'])) : '';
	Config::set('twitter', 'consumerkey', $consumerkey);
	Config::set('twitter', 'consumersecret', $consumersecret);
	info(L10n::t('Settings updated.') . EOL);
}

function twitter_addon_admin(App $a, &$o)
{
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/twitter/");

	$o = Renderer::replaceMacros($t, [
		'$submit' => L10n::t('Save Settings'),
		// name, label, value, help, [extra values]
		'$consumerkey' => ['consumerkey', L10n::t('Consumer key'), Config::get('twitter', 'consumerkey'), ''],
		'$consumersecret' => ['consumersecret', L10n::t('Consumer secret'), Config::get('twitter', 'consumersecret'), ''],
	]);
}

function twitter_cron(App $a)
{
	$last = Config::get('twitter', 'last_poll');

	$poll_interval = intval(Config::get('twitter', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = TWITTER_DEFAULT_POLL_INTERVAL;
	}

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			Logger::notice('twitter: poll intervall not reached');
			return;
		}
	}
	Logger::notice('twitter: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'mirror_posts' AND `v` = '1'");
	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			Logger::notice('Fetching', ['user' => $rr['uid']]);
			Worker::add(['priority' => PRIORITY_MEDIUM, 'force_priority' => true], "addon/twitter/twitter_sync.php", 1, (int) $rr['uid']);
		}
	}

	$abandon_days = intval(Config::get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'import' AND `v` = '1'");
	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			if ($abandon_days != 0) {
				$user = q("SELECT `login_date` FROM `user` WHERE uid=%d AND `login_date` >= '%s'", $rr['uid'], $abandon_limit);
				if (!DBA::isResult($user)) {
					Logger::notice('abandoned account: timeline from user will not be imported', ['user' => $rr['uid']]);
					continue;
				}
			}

			Logger::notice('importing timeline', ['user' => $rr['uid']]);
			Worker::add(['priority' => PRIORITY_MEDIUM, 'force_priority' => true], "addon/twitter/twitter_sync.php", 2, (int) $rr['uid']);
			/*
			  // To-Do
			  // check for new contacts once a day
			  $last_contact_check = PConfig::get($rr['uid'],'pumpio','contact_check');
			  if($last_contact_check)
			  $next_contact_check = $last_contact_check + 86400;
			  else
			  $next_contact_check = 0;

			  if($next_contact_check <= time()) {
			  pumpio_getallusers($a, $rr["uid"]);
			  PConfig::set($rr['uid'],'pumpio','contact_check',time());
			  }
			 */
		}
	}

	Logger::notice('twitter: cron_end');

	Config::set('twitter', 'last_poll', time());
}

function twitter_expire(App $a)
{
	$days = Config::get('twitter', 'expire');

	if ($days == 0) {
		return;
	}

	$r = Item::select(['id'], ['deleted' => true, 'network' => Protocol::TWITTER]);
	while ($row = DBA::fetch($r)) {
		DBA::delete('item', ['id' => $row['id']]);
	}
	DBA::close($r);

	Logger::notice('twitter_expire: expire_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'import' AND `v` = '1' ORDER BY RAND()");
	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			Logger::notice('twitter_expire', ['user' => $rr['uid']]);
			Item::expire($rr['uid'], $days, Protocol::TWITTER, true);
		}
	}

	Logger::notice('twitter_expire: expire_end');
}

function twitter_prepare_body(App $a, array &$b)
{
	if ($b["item"]["network"] != Protocol::TWITTER) {
		return;
	}

	if ($b["preview"]) {
		$max_char = 280;
		$item = $b["item"];
		$item["plink"] = AppR::baseUrl()->get() . "/display/" . $item["guid"];

		$condition = ['uri' => $item["thr-parent"], 'uid' => local_user()];
		$orig_post = Item::selectFirst(['author-link'], $condition);
		if (DBA::isResult($orig_post)) {
			$nicknameplain = preg_replace("=https?://twitter.com/(.*)=ism", "$1", $orig_post["author-link"]);
			$nickname = "@[url=" . $orig_post["author-link"] . "]" . $nicknameplain . "[/url]";
			$nicknameplain = "@" . $nicknameplain;

			if ((strpos($item["body"], $nickname) === false) && (strpos($item["body"], $nicknameplain) === false)) {
				$item["body"] = $nickname . " " . $item["body"];
			}
		}

		$msgarr = ItemContent::getPlaintextPost($item, $max_char, true, 8);
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

/**
 * @brief Build the item array for the mirrored post
 *
 * @param App $a Application class
 * @param integer $uid User id
 * @param object $post Twitter object with the post
 *
 * @return array item data to be posted
 */
function twitter_do_mirrorpost(App $a, $uid, $post)
{
	$datarray['api_source'] = true;
	$datarray['profile_uid'] = $uid;
	$datarray['extid'] = Protocol::TWITTER;
	$datarray['message_id'] = Item::newURI($uid, Protocol::TWITTER . ':' . $post->id);
	$datarray['protocol'] = Conversation::PARCEL_TWITTER;
	$datarray['source'] = json_encode($post);
	$datarray['title'] = '';

	if (!empty($post->retweeted_status)) {
		// We don't support nested shares, so we mustn't show quotes as shares on retweets
		$item = twitter_createpost($a, $uid, $post->retweeted_status, ['id' => 0], false, false, true);

		if (empty($item['body'])) {
			return [];
		}

		$datarray['body'] = "\n" . share_header(
			$item['author-name'],
			$item['author-link'],
			$item['author-avatar'],
			'',
			$item['created'],
			$item['plink']
		);

		$datarray['body'] .= $item['body'] . '[/share]';
	} else {
		$item = twitter_createpost($a, $uid, $post, ['id' => 0], false, false, false);

		if (empty($item['body'])) {
			return [];
		}

		$datarray['body'] = $item['body'];
	}

	$datarray['source'] = $item['app'];
	$datarray['verb'] = $item['verb'];

	if (isset($item['location'])) {
		$datarray['location'] = $item['location'];
	}

	if (isset($item['coord'])) {
		$datarray['coord'] = $item['coord'];
	}

	return $datarray;
}

function twitter_fetchtimeline(App $a, $uid)
{
	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');
	$lastid  = PConfig::get($uid, 'twitter', 'lastid');

	$application_name = Config::get('twitter', 'application_name');

	if ($application_name == "") {
		$application_name = AppR::baseUrl()->getHostname();
	}

	$has_picture = false;

	require_once 'mod/item.php';
	require_once 'mod/share.php';

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

	$parameters = ["exclude_replies" => true, "trim_user" => false, "contributor_details" => true, "include_rts" => true, "tweet_mode" => "extended", "include_ext_alt_text" => true];

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	try {
		$items = $connection->get('statuses/user_timeline', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::notice('Error fetching timeline', ['user' => $uid, 'message' => $e->getMessage()]);
		return;
	}

	if (!is_array($items)) {
		Logger::notice('No items', ['user' => $uid]);
		return;
	}

	$posts = array_reverse($items);

	Logger::log('Starting from ID ' . $lastid . ' for user ' . $uid, Logger::DEBUG);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid) {
				$lastid = $post->id_str;
				PConfig::set($uid, 'twitter', 'lastid', $lastid);
			}

			if ($first_time) {
				continue;
			}

			if (!stristr($post->source, $application_name)) {
				$_SESSION["authenticated"] = true;
				$_SESSION["uid"] = $uid;

				Logger::log('Preparing Twitter ID ' . $post->id_str . ' for user ' . $uid, Logger::DEBUG);

				$_REQUEST = twitter_do_mirrorpost($a, $uid, $post);

				if (empty($_REQUEST['body'])) {
					continue;
				}

				Logger::log('Posting Twitter ID ' . $post->id_str . ' for user ' . $uid);

				item_post($a);
			}
		}
	}
	PConfig::set($uid, 'twitter', 'lastid', $lastid);
	Logger::log('Last ID for user ' . $uid . ' is now ' . $lastid, Logger::DEBUG);
}

function twitter_fix_avatar($avatar)
{
	$new_avatar = str_replace("_normal.", ".", $avatar);

	$info = Images::getInfoFromURLCached($new_avatar);
	if (!$info) {
		$new_avatar = $avatar;
	}

	return $new_avatar;
}

function twitter_fetch_contact($uid, $data, $create_user)
{
	if (empty($data->id_str)) {
		return -1;
	}

	$avatar = twitter_fix_avatar($data->profile_image_url_https);
	$url = "https://twitter.com/" . $data->screen_name;
	$addr = $data->screen_name . "@twitter.com";

	$fields = ['url' => $url, 'network' => Protocol::TWITTER,
		'name' => $data->name, 'nick' => $data->screen_name, 'addr' => $addr,
                'location' => $data->location, 'about' => $data->description];

	$cid = Contact::getIdForURL($url, 0, true, $fields);
	if (!empty($cid)) {
		DBA::update('contact', $fields, ['id' => $cid]);
		Contact::updateAvatar($avatar, 0, $cid);
	}

	$contact = DBA::selectFirst('contact', [], ['uid' => $uid, 'alias' => "twitter::" . $data->id_str]);
	if (!DBA::isResult($contact) && !$create_user) {
		Logger::info('User contact not found', ['uid' => $uid, 'twitter-id' => $data->id_str]);
		return 0;
	}

	if (!DBA::isResult($contact)) {
		// create contact record
		$fields['uid'] = $uid;
		$fields['created'] = DateTimeFormat::utcNow();
		$fields['nurl'] = Strings::normaliseLink($url);
		$fields['alias'] = 'twitter::' . $data->id_str;
		$fields['poll'] = 'twitter::' . $data->id_str;
		$fields['rel'] = Contact::FRIEND;
		$fields['priority'] = 1;
		$fields['writable'] = true;
		$fields['blocked'] = false;
		$fields['readonly'] = false;
		$fields['pending'] = false;

		if (!DBA::insert('contact', $fields)) {
			return false;
		}

		$contact_id = DBA::lastInsertId();

		Group::addMember(User::getDefaultGroup($uid), $contact_id);

		Contact::updateAvatar($avatar, $uid, $contact_id);
	} else {
		if ($contact["readonly"] || $contact["blocked"]) {
			Logger::log("twitter_fetch_contact: Contact '" . $contact["nick"] . "' is blocked or readonly.", Logger::DEBUG);
			return -1;
		}

		$contact_id = $contact['id'];

		// update profile photos once every twelve hours as we have no notification of when they change.
		$update_photo = ($contact['avatar-date'] < DateTimeFormat::utc('now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion
		if (empty($contact['photo']) || empty($contact['thumb']) || empty($contact['micro']) || $update_photo) {
			Logger::log("twitter_fetch_contact: Updating contact " . $data->screen_name, Logger::DEBUG);

			Contact::updateAvatar($avatar, $uid, $contact['id']);

			$fields['name-date'] = DateTimeFormat::utcNow();
			$fields['uri-date'] = DateTimeFormat::utcNow();

			DBA::update('contact', $fields, ['id' => $contact['id']]);
		}
	}

	return $contact_id;
}

function twitter_fetchuser(App $a, $uid, $screen_name = "", $user_id = "")
{
	$ckey = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');

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
	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
	try {
		$user = $connection->get('users/show', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::log('twitter_fetchuser: Error fetching user ' . $uid . ': ' . $e->getMessage());
		return;
	}

	if (!is_object($user)) {
		return;
	}

	$contact_id = twitter_fetch_contact($uid, $user, true);

	return $contact_id;
}

function twitter_expand_entities(App $a, $body, $item, $picture)
{
	$plain = $body;

	$tags_arr = [];

	foreach ($item->entities->hashtags AS $hashtag) {
		$url = '#[url=' . AppR::baseUrl()->get() . '/search?tag=' . $hashtag->text . ']' . $hashtag->text . '[/url]';
		$tags_arr['#' . $hashtag->text] = $url;
		$body = str_replace('#' . $hashtag->text, $url, $body);
	}

	foreach ($item->entities->user_mentions AS $mention) {
		$url = '@[url=https://twitter.com/' . rawurlencode($mention->screen_name) . ']' . $mention->screen_name . '[/url]';
		$tags_arr['@' . $mention->screen_name] = $url;
		$body = str_replace('@' . $mention->screen_name, $url, $body);
	}

	if (isset($item->entities->urls)) {
		$type = '';
		$footerurl = '';
		$footerlink = '';
		$footer = '';

		foreach ($item->entities->urls as $url) {
			$plain = str_replace($url->url, '', $plain);

			if ($url->url && $url->expanded_url && $url->display_url) {
				// Quote tweet, we just remove the quoted tweet URL from the body, the share block will be added later.
				if (!empty($item->quoted_status) && isset($item->quoted_status_id_str)
					&& substr($url->expanded_url, -strlen($item->quoted_status_id_str)) == $item->quoted_status_id_str ) {
					$body = str_replace($url->url, '', $body);
					continue;
				}

				$expanded_url = $url->expanded_url;

				$final_url = Network::finalUrl($url->expanded_url);

				$oembed_data = OEmbed::fetchURL($final_url);

				if (empty($oembed_data) || empty($oembed_data->type)) {
					continue;
				}

				// Quickfix: Workaround for URL with '[' and ']' in it
				if (strpos($expanded_url, '[') || strpos($expanded_url, ']')) {
					$expanded_url = $url->url;
				}

				if ($type == '') {
					$type = $oembed_data->type;
				}

				if ($oembed_data->type == 'video') {
					$type = $oembed_data->type;
					$footerurl = $expanded_url;
					$footerlink = '[url=' . $expanded_url . ']' . $url->display_url . '[/url]';

					$body = str_replace($url->url, $footerlink, $body);
				} elseif (($oembed_data->type == 'photo') && isset($oembed_data->url)) {
					$body = str_replace($url->url, '[url=' . $expanded_url . '][img]' . $oembed_data->url . '[/img][/url]', $body);
				} elseif ($oembed_data->type != 'link') {
					$body = str_replace($url->url, '[url=' . $expanded_url . ']' . $url->display_url . '[/url]', $body);
				} else {
					$img_str = Network::fetchUrl($final_url, true, 4);

					$tempfile = tempnam(get_temppath(), 'cache');
					file_put_contents($tempfile, $img_str);

					// See http://php.net/manual/en/function.exif-imagetype.php#79283
					if (filesize($tempfile) > 11) {
						$mime = image_type_to_mime_type(exif_imagetype($tempfile));
					} else {
						$mime = false;
					}

					unlink($tempfile);

					if (substr($mime, 0, 6) == 'image/') {
						$type = 'photo';
						$body = str_replace($url->url, '[img]' . $final_url . '[/img]', $body);
					} else {
						$type = $oembed_data->type;
						$footerurl = $expanded_url;
						$footerlink = '[url=' . $expanded_url . ']' . $url->display_url . '[/url]';

						$body = str_replace($url->url, $footerlink, $body);
					}
				}
			}
		}

		// Footer will be taken care of with a share block in the case of a quote
		if (empty($item->quoted_status)) {
			if ($footerurl != '') {
				$footer = add_page_info($footerurl, false, $picture);
			}

			if (($footerlink != '') && (trim($footer) != '')) {
				$removedlink = trim(str_replace($footerlink, '', $body));

				if (($removedlink == '') || strstr($body, $removedlink)) {
					$body = $removedlink;
				}

				$body .= $footer;
			}

			if ($footer == '' && $picture != '') {
				$body .= "\n\n[img]" . $picture . "[/img]\n";
			} elseif ($footer == '' && $picture == '') {
				$body = add_page_info_to_body($body);
			}
		}
	}

	// it seems as if the entities aren't always covering all mentions. So the rest will be checked here
	$tags = BBCode::getTags($body);

	if (count($tags)) {
		foreach ($tags as $tag) {
			if (strstr(trim($tag), ' ')) {
				continue;
			}

			if (strpos($tag, '#') === 0) {
				if (strpos($tag, '[url=')) {
					continue;
				}

				// don't link tags that are already embedded in links
				if (preg_match('/\[(.*?)' . preg_quote($tag, '/') . '(.*?)\]/', $body)) {
					continue;
				}
				if (preg_match('/\[(.*?)\]\((.*?)' . preg_quote($tag, '/') . '(.*?)\)/', $body)) {
					continue;
				}

				$basetag = str_replace('_', ' ', substr($tag, 1));
				$url = '#[url=' . AppR::baseUrl()->get() . '/search?tag=' . $basetag . ']' . $basetag . '[/url]';
				$body = str_replace($tag, $url, $body);
				$tags_arr['#' . $basetag] = $url;
			} elseif (strpos($tag, '@') === 0) {
				if (strpos($tag, '[url=')) {
					continue;
				}

				$basetag = substr($tag, 1);
				$url = '@[url=https://twitter.com/' . rawurlencode($basetag) . ']' . $basetag . '[/url]';
				$body = str_replace($tag, $url, $body);
				$tags_arr['@' . $basetag] = $url;
			}
		}
	}

	$tags = implode($tags_arr, ',');

	return ['body' => $body, 'tags' => $tags, 'plain' => $plain];
}

/**
 * @brief Fetch media entities and add media links to the body
 *
 * @param object $post Twitter object with the post
 * @param array $postarray Array of the item that is about to be posted
 *
 * @return $picture string Image URL or empty string
 */
function twitter_media_entities($post, array &$postarray)
{
	// There are no media entities? So we quit.
	if (empty($post->extended_entities->media)) {
		return '';
	}

	// When the post links to an external page, we only take one picture.
	// We only do this when there is exactly one media.
	if ((count($post->entities->urls) > 0) && (count($post->extended_entities->media) == 1)) {
		$medium = $post->extended_entities->media[0];
		$picture = '';
		foreach ($post->entities->urls as $link) {
			// Let's make sure the external link url matches the media url
			if ($medium->url == $link->url && isset($medium->media_url_https)) {
				$picture = $medium->media_url_https;
				$postarray['body'] = str_replace($medium->url, '', $postarray['body']);
				return $picture;
			}
		}
	}

	// This is a pure media post, first search for all media urls
	$media = [];
	foreach ($post->extended_entities->media AS $medium) {
		if (!isset($media[$medium->url])) {
			$media[$medium->url] = '';
		}
		switch ($medium->type) {
			case 'photo':
				if (!empty($medium->ext_alt_text)) {
					Logger::info('Got text description', ['alt_text' => $medium->ext_alt_text]);
					$media[$medium->url] .= "\n[img=" . $medium->media_url_https .']' . $medium->ext_alt_text . '[/img]';
				} else {
					$media[$medium->url] .= "\n[img]" . $medium->media_url_https . '[/img]';
				}

				$postarray['object-type'] = Activity\ObjectType::IMAGE;
				break;
			case 'video':
			case 'animated_gif':
				if (!empty($medium->ext_alt_text)) {
					Logger::info('Got text description', ['alt_text' => $medium->ext_alt_text]);
					$media[$medium->url] .= "\n[img=" . $medium->media_url_https .']' . $medium->ext_alt_text . '[/img]';
				} else {
					$media[$medium->url] .= "\n[img]" . $medium->media_url_https . '[/img]';
				}

				$postarray['object-type'] = Activity\ObjectType::VIDEO;
				if (is_array($medium->video_info->variants)) {
					$bitrate = 0;
					// We take the video with the highest bitrate
					foreach ($medium->video_info->variants AS $variant) {
						if (($variant->content_type == 'video/mp4') && ($variant->bitrate >= $bitrate)) {
							$media[$medium->url] = "\n[video]" . $variant->url . '[/video]';
							$bitrate = $variant->bitrate;
						}
					}
				}
				break;
			// The following code will only be activated for test reasons
			//default:
			//	$postarray['body'] .= print_r($medium, true);
		}
	}

	// Now we replace the media urls.
	foreach ($media AS $key => $value) {
		$postarray['body'] = str_replace($key, "\n" . $value . "\n", $postarray['body']);
	}

	return '';
}

function twitter_createpost(App $a, $uid, $post, array $self, $create_user, $only_existing_contact, $noquote)
{
	$postarray = [];
	$postarray['network'] = Protocol::TWITTER;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['uri'] = "twitter::" . $post->id_str;
	$postarray['protocol'] = Conversation::PARCEL_TWITTER;
	$postarray['source'] = json_encode($post);

	// Don't import our own comments
	if (Item::exists(['extid' => $postarray['uri'], 'uid' => $uid])) {
		Logger::log("Item with extid " . $postarray['uri'] . " found.", Logger::DEBUG);
		return [];
	}

	$contactid = 0;

	if ($post->in_reply_to_status_id_str != "") {
		$parent = "twitter::" . $post->in_reply_to_status_id_str;

		$fields = ['uri', 'parent-uri', 'parent'];
		$parent_item = Item::selectFirst($fields, ['uri' => $parent, 'uid' => $uid]);
		if (!DBA::isResult($parent_item)) {
			$parent_item = Item::selectFirst($fields, ['extid' => $parent, 'uid' => $uid]);
		}

		if (DBA::isResult($parent_item)) {
			$postarray['thr-parent'] = $parent_item['uri'];
			$postarray['parent-uri'] = $parent_item['parent-uri'];
			$postarray['parent'] = $parent_item['parent'];
			$postarray['object-type'] = Activity\ObjectType::COMMENT;
		} else {
			$postarray['thr-parent'] = $postarray['uri'];
			$postarray['parent-uri'] = $postarray['uri'];
			$postarray['object-type'] = Activity\ObjectType::NOTE;
		}

		// Is it me?
		$own_id = PConfig::get($uid, 'twitter', 'own_id');

		if ($post->user->id_str == $own_id) {
			$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
				intval($uid));

			if (DBA::isResult($r)) {
				$contactid = $r[0]["id"];

				$postarray['owner-name']   = $r[0]["name"];
				$postarray['owner-link']   = $r[0]["url"];
				$postarray['owner-avatar'] = $r[0]["photo"];
			} else {
				Logger::log("No self contact for user " . $uid, Logger::DEBUG);
				return [];
			}
		}
		// Don't create accounts of people who just comment something
		$create_user = false;
	} else {
		$postarray['parent-uri'] = $postarray['uri'];
		$postarray['object-type'] = Activity\ObjectType::NOTE;
	}

	if ($contactid == 0) {
		$contactid = twitter_fetch_contact($uid, $post->user, $create_user);

		$postarray['owner-name'] = $post->user->name;
		$postarray['owner-link'] = "https://twitter.com/" . $post->user->screen_name;
		$postarray['owner-avatar'] = twitter_fix_avatar($post->user->profile_image_url_https);
	}

	if (($contactid == 0) && !$only_existing_contact) {
		$contactid = $self['id'];
	} elseif ($contactid <= 0) {
		Logger::log("Contact ID is zero or less than zero.", Logger::DEBUG);
		return [];
	}

	$postarray['contact-id'] = $contactid;

	$postarray['verb'] = Activity::POST;
	$postarray['author-name'] = $postarray['owner-name'];
	$postarray['author-link'] = $postarray['owner-link'];
	$postarray['author-avatar'] = $postarray['owner-avatar'];
	$postarray['plink'] = "https://twitter.com/" . $post->user->screen_name . "/status/" . $post->id_str;
	$postarray['app'] = strip_tags($post->source);

	if ($post->user->protected) {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self['id'] . '>';
	} else {
		$postarray['private'] = 0;
		$postarray['allow_cid'] = '';
	}

	if (!empty($post->full_text)) {
		$postarray['body'] = $post->full_text;
	} else {
		$postarray['body'] = $post->text;
	}

	// When the post contains links then use the correct object type
	if (count($post->entities->urls) > 0) {
		$postarray['object-type'] = Activity\ObjectType::BOOKMARK;
	}

	// Search for media links
	$picture = twitter_media_entities($post, $postarray);

	$converted = twitter_expand_entities($a, $postarray['body'], $post, $picture);
	$postarray['body'] = $converted["body"];
	$postarray['tag'] = $converted["tags"];
	$postarray['created'] = DateTimeFormat::utc($post->created_at);
	$postarray['edited'] = DateTimeFormat::utc($post->created_at);

	$statustext = $converted["plain"];

	if (!empty($post->place->name)) {
		$postarray["location"] = $post->place->name;
	}
	if (!empty($post->place->full_name)) {
		$postarray["location"] = $post->place->full_name;
	}
	if (!empty($post->geo->coordinates)) {
		$postarray["coord"] = $post->geo->coordinates[0] . " " . $post->geo->coordinates[1];
	}
	if (!empty($post->coordinates->coordinates)) {
		$postarray["coord"] = $post->coordinates->coordinates[1] . " " . $post->coordinates->coordinates[0];
	}
	if (!empty($post->retweeted_status)) {
		$retweet = twitter_createpost($a, $uid, $post->retweeted_status, $self, false, false, $noquote);

		if (empty($retweet['body'])) {
			return [];
		}

		if (!$noquote) {
			// Store the original tweet
			Item::insert($retweet);

			// CHange the other post into a reshare activity
			$postarray['verb'] = Activity::ANNOUNCE;
			$postarray['gravity'] = GRAVITY_ACTIVITY;
			$postarray['object-type'] = Activity\ObjectType::NOTE;

			$postarray['thr-parent'] = $retweet['uri'];
			$postarray['parent-uri'] = $retweet['uri'];
		} else {
			$retweet['source'] = $postarray['source'];
			$retweet['private'] = $postarray['private'];
			$retweet['allow_cid'] = $postarray['allow_cid'];
			$retweet['contact-id'] = $postarray['contact-id'];
			$retweet['owner-name'] = $postarray['owner-name'];
			$retweet['owner-link'] = $postarray['owner-link'];
			$retweet['owner-avatar'] = $postarray['owner-avatar'];

			$postarray = $retweet;
		}
	}

	if (!empty($post->quoted_status) && !$noquote) {
		$quoted = twitter_createpost($a, $uid, $post->quoted_status, $self, false, false, true);

		if (!empty($quoted['body'])) {
			$postarray['body'] .= "\n" . share_header(
				$quoted['author-name'],
				$quoted['author-link'],
				$quoted['author-avatar'],
				"",
				$quoted['created'],
				$quoted['plink']
			);

			$postarray['body'] .= $quoted['body'] . '[/share]';
		} else {
			// Quoted post author is blocked/ignored, so we just provide the link to avoid removing quote context.
			$postarray['body'] .= "\n\nhttps://twitter.com/" . $post->quoted_status->user->screen_name . "/status/" . $post->quoted_status->id_str;
		}
	}

	return $postarray;
}

function twitter_fetchparentposts(App $a, $uid, $post, TwitterOAuth $connection, array $self)
{
	Logger::log("twitter_fetchparentposts: Fetching for user " . $uid . " and post " . $post->id_str, Logger::DEBUG);

	$posts = [];

	while (!empty($post->in_reply_to_status_id_str)) {
		$parameters = ["trim_user" => false, "tweet_mode" => "extended", "id" => $post->in_reply_to_status_id_str, "include_ext_alt_text" => true];

		try {
			$post = $connection->get('statuses/show', $parameters);
		} catch (TwitterOAuthException $e) {
			Logger::log('twitter_fetchparentposts: Error fetching for user ' . $uid . ' and post ' . $post->id_str . ': ' . $e->getMessage());
			break;
		}

		if (empty($post)) {
			Logger::log("twitter_fetchparentposts: Can't fetch post " . $parameters['id'], Logger::DEBUG);
			break;
		}

		if (empty($post->id_str)) {
			Logger::log("twitter_fetchparentposts: This is not a post " . json_encode($post), Logger::DEBUG);
			break;
		}

		if (Item::exists(['uri' => 'twitter::' . $post->id_str, 'uid' => $uid])) {
			break;
		}

		$posts[] = $post;
	}

	Logger::log("twitter_fetchparentposts: Fetching " . count($posts) . " parents", Logger::DEBUG);

	$posts = array_reverse($posts);

	if (!empty($posts)) {
		foreach ($posts as $post) {
			$postarray = twitter_createpost($a, $uid, $post, $self, false, !PConfig::get($uid, 'twitter', 'create_user'), false);

			if (empty($postarray['body'])) {
				continue;
			}

			$item = Item::insert($postarray);

			$postarray["id"] = $item;

			Logger::log('twitter_fetchparentpost: User ' . $self["nick"] . ' posted parent timeline item ' . $item);
		}
	}
}

function twitter_fetchhometimeline(App $a, $uid)
{
	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');
	$create_user = PConfig::get($uid, 'twitter', 'create_user');
	$mirror_posts = PConfig::get($uid, 'twitter', 'mirror_posts');

	Logger::log("Fetching timeline for user " . $uid, Logger::DEBUG);

	$application_name = Config::get('twitter', 'application_name');

	if ($application_name == "") {
		$application_name = AppR::baseUrl()->getHostname();
	}

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

	try {
		$own_contact = twitter_fetch_own_contact($a, $uid);
	} catch (TwitterOAuthException $e) {
		Logger::log('Error fetching own contact for user ' . $uid . ': ' . $e->getMessage());
		return;
	}

	$r = q("SELECT * FROM `contact` WHERE `id` = %d AND `uid` = %d LIMIT 1",
		intval($own_contact),
		intval($uid));

	if (DBA::isResult($r)) {
		$own_id = $r[0]["nick"];
	} else {
		Logger::log("Own twitter contact not found for user " . $uid);
		return;
	}

	$self = User::getOwnerDataById($uid);
	if ($self === false) {
		Logger::log("Own contact not found for user " . $uid);
		return;
	}

	$parameters = ["exclude_replies" => false, "trim_user" => false, "contributor_details" => true, "include_rts" => true, "tweet_mode" => "extended", "include_ext_alt_text" => true];
	//$parameters["count"] = 200;
	// Fetching timeline
	$lastid = PConfig::get($uid, 'twitter', 'lasthometimelineid');

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	try {
		$items = $connection->get('statuses/home_timeline', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::log('Error fetching home timeline for user ' . $uid . ': ' . $e->getMessage());
		return;
	}

	if (!is_array($items)) {
		Logger::log('No array while fetching home timeline for user ' . $uid . ': ' . print_r($items, true));
		return;
	}

	if (empty($items)) {
		Logger::log('No new timeline content for user ' . $uid, Logger::INFO);
		return;
	}

	$posts = array_reverse($items);

	Logger::log('Fetching timeline from ID ' . $lastid . ' for user ' . $uid . ' ' . sizeof($posts) . ' items', Logger::DEBUG);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid) {
				$lastid = $post->id_str;
				PConfig::set($uid, 'twitter', 'lasthometimelineid', $lastid);
			}

			if ($first_time) {
				continue;
			}

			if (stristr($post->source, $application_name) && $post->user->screen_name == $own_id) {
				Logger::log("Skip previously sent post", Logger::DEBUG);
				continue;
			}

			if ($mirror_posts && $post->user->screen_name == $own_id && $post->in_reply_to_status_id_str == "") {
				Logger::log("Skip post that will be mirrored", Logger::DEBUG);
				continue;
			}

			if ($post->in_reply_to_status_id_str != "") {
				twitter_fetchparentposts($a, $uid, $post, $connection, $self);
			}

			Logger::log('Preparing post ' . $post->id_str . ' for user ' . $uid, Logger::DEBUG);

			$postarray = twitter_createpost($a, $uid, $post, $self, $create_user, true, false);

			if (empty($postarray['body']) || trim($postarray['body']) == "") {
				Logger::log('Empty body for post ' . $post->id_str . ' and user ' . $uid, Logger::DEBUG);
				continue;
			}

			$notify = false;

			if (($postarray['uri'] == $postarray['parent-uri']) && ($postarray['author-link'] == $postarray['owner-link'])) {
				$contact = DBA::selectFirst('contact', [], ['id' => $postarray['contact-id'], 'self' => false]);
				if (DBA::isResult($contact)) {
					$notify = Item::isRemoteSelf($contact, $postarray);
				}
			}

			$item = Item::insert($postarray, false, $notify);
			$postarray["id"] = $item;

			Logger::log('User ' . $uid . ' posted home timeline item ' . $item);
		}
	}
	PConfig::set($uid, 'twitter', 'lasthometimelineid', $lastid);

	Logger::log('Last timeline ID for user ' . $uid . ' is now ' . $lastid, Logger::DEBUG);

	// Fetching mentions
	$lastid = PConfig::get($uid, 'twitter', 'lastmentionid');

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	try {
		$items = $connection->get('statuses/mentions_timeline', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::log('Error fetching mentions: ' . $e->getMessage());
		return;
	}

	if (!is_array($items)) {
		Logger::log("Error fetching mentions: " . print_r($items, true), Logger::DEBUG);
		return;
	}

	$posts = array_reverse($items);

	Logger::log("Fetching mentions for user " . $uid . " " . sizeof($posts) . " items", Logger::DEBUG);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid) {
				$lastid = $post->id_str;
			}

			if ($first_time) {
				continue;
			}

			if ($post->in_reply_to_status_id_str != "") {
				twitter_fetchparentposts($a, $uid, $post, $connection, $self);
			}

			$postarray = twitter_createpost($a, $uid, $post, $self, false, !$create_user, false);

			if (empty($postarray['body'])) {
				continue;
			}

			$item = Item::insert($postarray);

			Logger::log('User ' . $uid . ' posted mention timeline item ' . $item);
		}
	}

	PConfig::set($uid, 'twitter', 'lastmentionid', $lastid);

	Logger::log('Last mentions ID for user ' . $uid . ' is now ' . $lastid, Logger::DEBUG);
}

function twitter_fetch_own_contact(App $a, $uid)
{
	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');

	$own_id = PConfig::get($uid, 'twitter', 'own_id');

	$contact_id = 0;

	if ($own_id == "") {
		$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

		// Fetching user data
		// get() may throw TwitterOAuthException, but we will catch it later
		$user = $connection->get('account/verify_credentials');
		if (empty($user) || empty($user->id_str)) {
			return false;
		}

		PConfig::set($uid, 'twitter', 'own_id', $user->id_str);

		$contact_id = twitter_fetch_contact($uid, $user, true);
	} else {
		$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
			intval($uid),
			DBA::escape("twitter::" . $own_id));
		if (DBA::isResult($r)) {
			$contact_id = $r[0]["id"];
		} else {
			PConfig::delete($uid, 'twitter', 'own_id');
		}
	}

	return $contact_id;
}

function twitter_is_retweet(App $a, $uid, $body)
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

	$id = preg_replace("=https?://twitter.com/(.*)/status/(.*)=ism", "$2", $link);
	if ($id == $link) {
		return false;
	}

	Logger::log('twitter_is_retweet: Retweeting id ' . $id . ' for user ' . $uid, Logger::DEBUG);

	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
	$result = $connection->post('statuses/retweet/' . $id);

	Logger::log('twitter_is_retweet: result ' . print_r($result, true), Logger::DEBUG);

	return !isset($result->errors);
}

function twitter_update_mentions($body)
{
	$URLSearchString = "^\[\]";
	$return = preg_replace_callback(
		"/@\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism",
		function ($matches) {
			if (strpos($matches[1], 'twitter.com')) {
				$return = '@' . substr($matches[1], strrpos($matches[1], '/') + 1);
			} else {
				$return = $matches[2] . ' (' . $matches[1] . ')';
			}

			return $return;
		},
		$body
	);

	return $return;
}

function twitter_convert_share(array $attributes, array $author_contact, $content, $is_quote_share)
{
	if ($author_contact['network'] == Protocol::TWITTER) {
		$mention = '@' . $author_contact['nick'];
	} else {
		$mention = $author_contact['addr'];
	}

	return ($is_quote_share ? "\n\n" : '' ) . 'RT ' . $mention . ': ' . $content . "\n\n" . $attributes['link'];
}
