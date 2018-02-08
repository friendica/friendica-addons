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
 *     Add this key pair to your global .htconfig.php or use the admin panel.
 *
 *     $a->config['twitter']['consumerkey'] = 'your consumer_key here';
 *     $a->config['twitter']['consumersecret'] = 'your consumer_secret here';
 *
 *     To activate the addon itself add it to the $a->config['system']['addon']
 *     setting. After this, your user can configure their Twitter account settings
 *     from "Settings -> Addon Settings".
 *
 *     Requirements: PHP5, curl
 */

use Abraham\TwitterOAuth\TwitterOAuth;
use Friendica\App;
use Friendica\Content\OEmbed;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;
use Friendica\Core\Worker;
use Friendica\Model\GContact;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\Photo;
use Friendica\Model\Queue;
use Friendica\Model\User;
use Friendica\Object\Image;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;

require_once 'boot.php';
require_once 'include/dba.php';
require_once 'include/enotify.php';
require_once 'include/text.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

define('TWITTER_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function twitter_install()
{
	//  we need some hooks, for the configuration and for sending tweets
	Addon::registerHook('connector_settings', 'addon/twitter/twitter.php', 'twitter_settings');
	Addon::registerHook('connector_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
	Addon::registerHook('post_local', 'addon/twitter/twitter.php', 'twitter_post_local');
	Addon::registerHook('notifier_normal', 'addon/twitter/twitter.php', 'twitter_post_hook');
	Addon::registerHook('jot_networks', 'addon/twitter/twitter.php', 'twitter_jot_nets');
	Addon::registerHook('cron', 'addon/twitter/twitter.php', 'twitter_cron');
	Addon::registerHook('queue_predeliver', 'addon/twitter/twitter.php', 'twitter_queue_hook');
	Addon::registerHook('follow', 'addon/twitter/twitter.php', 'twitter_follow');
	Addon::registerHook('expire', 'addon/twitter/twitter.php', 'twitter_expire');
	Addon::registerHook('prepare_body', 'addon/twitter/twitter.php', 'twitter_prepare_body');
	Addon::registerHook('check_item_notification', 'addon/twitter/twitter.php', 'twitter_check_item_notification');
	logger("installed twitter");
}

function twitter_uninstall()
{
	Addon::unregisterHook('connector_settings', 'addon/twitter/twitter.php', 'twitter_settings');
	Addon::unregisterHook('connector_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
	Addon::unregisterHook('post_local', 'addon/twitter/twitter.php', 'twitter_post_local');
	Addon::unregisterHook('notifier_normal', 'addon/twitter/twitter.php', 'twitter_post_hook');
	Addon::unregisterHook('jot_networks', 'addon/twitter/twitter.php', 'twitter_jot_nets');
	Addon::unregisterHook('cron', 'addon/twitter/twitter.php', 'twitter_cron');
	Addon::unregisterHook('queue_predeliver', 'addon/twitter/twitter.php', 'twitter_queue_hook');
	Addon::unregisterHook('follow', 'addon/twitter/twitter.php', 'twitter_follow');
	Addon::unregisterHook('expire', 'addon/twitter/twitter.php', 'twitter_expire');
	Addon::unregisterHook('prepare_body', 'addon/twitter/twitter.php', 'twitter_prepare_body');
	Addon::unregisterHook('check_item_notification', 'addon/twitter/twitter.php', 'twitter_check_item_notification');

	// old setting - remove only
	Addon::unregisterHook('post_local_end', 'addon/twitter/twitter.php', 'twitter_post_hook');
	Addon::unregisterHook('addon_settings', 'addon/twitter/twitter.php', 'twitter_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
}

function twitter_check_item_notification(App $a, &$notification_data)
{
	$own_id = PConfig::get($notification_data["uid"], 'twitter', 'own_id');

	$own_user = q("SELECT `url` FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
			intval($notification_data["uid"]),
			dbesc("twitter::".$own_id)
	);

	if ($own_user) {
		$notification_data["profiles"][] = $own_user[0]["url"];
	}
}

function twitter_follow(App $a, &$contact)
{
	logger("twitter_follow: Check if contact is twitter contact. " . $contact["url"], LOGGER_DEBUG);

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
				dbesc($nickname));
	if (count($r)) {
		$contact["contact"] = $r[0];
	}
}

function twitter_jot_nets(App $a, &$b)
{
	if (!local_user()) {
		return;
	}

	$tw_post = PConfig::get(local_user(), 'twitter', 'post');
	if (intval($tw_post) == 1) {
		$tw_defpost = PConfig::get(local_user(), 'twitter', 'post_by_default');
		$selected = ((intval($tw_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="twitter_enable"' . $selected . ' value="1" /> '
			. L10n::t('Post to Twitter') . '</div>';
	}
}

function twitter_settings_post(App $a, $post)
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
			logger('got a Twitter PIN');
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
			}
			//  reload the Addon Settings page, if we don't do it see Bug #42
			goaway('settings/connectors');
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
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/twitter/twitter.css' . '" media="all" />' . "\r\n";
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
		} else {
			/*			 * *
			 *  we have an OAuth key / secret pair for the user
			 *  so let's give a chance to disable the postings to Twitter
			 */
			$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
			$details = $connection->get('account/verify_credentials');

			$field_checkbox = get_markup_template('field_checkbox.tpl');

			$s .= '<div id="twitter-info" >
				<p>' . L10n::t('Currently connected to: ') . '<a href="https://twitter.com/' . $details->screen_name . '" target="_twitter">' . $details->screen_name . '</a>
					<button type="submit" name="twitter-disconnect" value="1">' . L10n::t('Disconnect') . '</button>
				</p>
				<p id="twitter-info-block">
					<a href="https://twitter.com/' . $details->screen_name . '" target="_twitter"><img id="twitter-avatar" src="' . $details->profile_image_url . '" /></a>
					<em>' . $details->description . '</em>
				</p>
			</div>';
			$s .= '<div class="clear"></div>';

			$s .= replace_macros($field_checkbox, [
				'$field' => ['twitter-enable', L10n::t('Allow posting to Twitter'), $enabled, L10n::t('If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.')]
			]);
			if ($a->user['hidewall']) {
				$s .= '<p>' . L10n::t('<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.') . '</p>';
			}
			$s .= replace_macros($field_checkbox, [
				'$field' => ['twitter-default', L10n::t('Send public postings to Twitter by default'), $defenabled, '']
			]);
			$s .= replace_macros($field_checkbox, [
				'$field' => ['twitter-mirror', L10n::t('Mirror all posts from twitter that are no replies'), $mirrorenabled, '']
			]);
			$s .= replace_macros($field_checkbox, [
				'$field' => ['twitter-import', L10n::t('Import the remote timeline'), $importenabled, '']
			]);
			$s .= replace_macros($field_checkbox, [
				'$field' => ['twitter-create_user', L10n::t('Automatically create contacts'), $create_userenabled, '']
			]);

			$s .= '<div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
		}
	}
	$s .= '</div><div class="clear"></div>';
}

function twitter_post_local(App $a, &$b)
{
	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	$twitter_post = intval(PConfig::get(local_user(), 'twitter', 'post'));
	$twitter_enable = (($twitter_post && x($_REQUEST, 'twitter_enable')) ? intval($_REQUEST['twitter_enable']) : 0);

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

	logger("twitter_action '" . $action . "' ID: " . $pid . " data: " . print_r($post, true), LOGGER_DATA);

	switch ($action) {
		case "delete":
			// To-Do: $result = $connection->post('statuses/destroy', $post);
			break;
		case "like":
			$result = $connection->post('favorites/create', $post);
			break;
		case "unlike":
			$result = $connection->post('favorites/destroy', $post);
			break;
	}
	logger("twitter_action '" . $action . "' send, result: " . print_r($result, true), LOGGER_DEBUG);
}

function twitter_post_hook(App $a, &$b)
{
	// Post to Twitter
	if (!PConfig::get($b["uid"], 'twitter', 'import')
		&& ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		logger("twitter_post_hook: parameter " . print_r($b, true), LOGGER_DATA);

		// Looking if its a reply to a twitter post
		if ((substr($b["parent-uri"], 0, 9) != "twitter::")
			&& (substr($b["extid"], 0, 9) != "twitter::")
			&& (substr($b["thr-parent"], 0, 9) != "twitter::"))
		{
			logger("twitter_post_hook: no twitter post " . $b["parent"]);
			return;
		}

		$r = q("SELECT * FROM item WHERE item.uri = '%s' AND item.uid = %d LIMIT 1",
			dbesc($b["thr-parent"]),
			intval($b["uid"]));

		if (!count($r)) {
			logger("twitter_post_hook: no parent found " . $b["thr-parent"]);
			return;
		} else {
			$iscomment = true;
			$orig_post = $r[0];
		}


		$nicknameplain = preg_replace("=https?://twitter.com/(.*)=ism", "$1", $orig_post["author-link"]);
		$nickname = "@[url=" . $orig_post["author-link"] . "]" . $nicknameplain . "[/url]";
		$nicknameplain = "@" . $nicknameplain;

		logger("twitter_post_hook: comparing " . $nickname . " and " . $nicknameplain . " with " . $b["body"], LOGGER_DEBUG);
		if ((strpos($b["body"], $nickname) === false) && (strpos($b["body"], $nicknameplain) === false)) {
			$b["body"] = $nickname . " " . $b["body"];
		}

		logger("twitter_post_hook: parent found " . print_r($orig_post, true), LOGGER_DATA);
	} else {
		$iscomment = false;

		if ($b['private'] || !strstr($b['postopts'], 'twitter')) {
			return;
		}

		// Dont't post if the post doesn't belong to us.
		// This is a check for forum postings
		$self = dba::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
		if ($b['contact-id'] != $self['id']) {
			return;
		}
	}

	if (($b['verb'] == ACTIVITY_POST) && $b['deleted']) {
		twitter_action($a, $b["uid"], substr($orig_post["uri"], 9), "delete");
	}

	if ($b['verb'] == ACTIVITY_LIKE) {
		logger("twitter_post_hook: parameter 2 " . substr($b["thr-parent"], 9), LOGGER_DEBUG);
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
	if ($b['extid'] == NETWORK_TWITTER) {
		return;
	}

	if ($b['app'] == "Twitter") {
		return;
	}

	logger('twitter post invoked');

	PConfig::load($b['uid'], 'twitter');

	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get($b['uid'], 'twitter', 'oauthtoken');
	$osecret = PConfig::get($b['uid'], 'twitter', 'oauthsecret');

	if ($ckey && $csecret && $otoken && $osecret) {
		logger('twitter: we have customer key and oauth stuff, going to send.', LOGGER_DEBUG);

		// If it's a repeated message from twitter then do a native retweet and exit
		if (twitter_is_retweet($a, $b['uid'], $b['body'])) {
			return;
		}

		$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

		$max_char = 280;
		$msgarr = BBCode::toPlaintext($b, $max_char, true, 8);
		$msg = $msgarr["text"];

		if (($msg == "") && isset($msgarr["title"])) {
			$msg = Plaintext::shorten($msgarr["title"], $max_char - 50);
		}

		$image = "";

		if (isset($msgarr["url"]) && ($msgarr["type"] != "photo")) {
			$msg .= "\n" . $msgarr["url"];
		}

		if (isset($msgarr["image"]) && ($msgarr["type"] != "video")) {
			$image = $msgarr["image"];
		}

		// and now tweet it :-)
		if (strlen($msg) && ($image != "")) {
			$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
			$media = $connection->upload('media/upload', ['media' => $image]);

			$post = ['status' => $msg, 'media_ids' => $media->media_id_string];

			if ($iscomment) {
				$post["in_reply_to_status_id"] = substr($orig_post["uri"], 9);
			}

			$result = $connection->post('statuses/update', $post);

			logger('twitter_post_with_media send, result: ' . print_r($result, true), LOGGER_DEBUG);

			if ($result->source) {
				Config::set("twitter", "application_name", strip_tags($result->source));
			}

			if ($result->errors || $result->error) {
				logger('Send to Twitter failed: "' . print_r($result->errors, true) . '"');

				// Workaround: Remove the picture link so that the post can be reposted without it
				$msg .= " " . $image;
				$image = "";
			} elseif ($iscomment) {
				logger('twitter_post: Update extid ' . $result->id_str . " for post id " . $b['id']);
				Item::update(['extid' => "twitter::" . $result->id_str, 'body' => $result->text], ['id' => $b['id']]);
			}
		}

		if (strlen($msg) && ($image == "")) {
// -----------------
			$max_char = 280;
			$msgarr = BBCode::toPlaintext($b, $max_char, true, 8);
			$msg = $msgarr["text"];

			if (($msg == "") && isset($msgarr["title"])) {
				$msg = Plaintext::shorten($msgarr["title"], $max_char - 50);
			}

			if (isset($msgarr["url"])) {
				$msg .= "\n" . $msgarr["url"];
			}
// -----------------
			$url = 'statuses/update';
			$post = ['status' => $msg, 'weighted_character_count' => 'true'];

			if ($iscomment) {
				$post["in_reply_to_status_id"] = substr($orig_post["uri"], 9);
			}

			$result = $connection->post($url, $post);
			logger('twitter_post send, result: ' . print_r($result, true), LOGGER_DEBUG);

			if ($result->source) {
				Config::set("twitter", "application_name", strip_tags($result->source));
			}

			if ($result->errors) {
				logger('Send to Twitter failed: "' . print_r($result->errors, true) . '"');

				$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", intval($b['uid']));
				if (count($r)) {
					$a->contact = $r[0]["id"];
				}

				$s = serialize(['url' => $url, 'item' => $b['id'], 'post' => $post]);

				Queue::add($a->contact, NETWORK_TWITTER, $s);
				notice(L10n::t('Twitter post failed. Queued for retry.') . EOL);
			} elseif ($iscomment) {
				logger('twitter_post: Update extid ' . $result->id_str . " for post id " . $b['id']);
				Item::update(['extid' => "twitter::" . $result->id_str], ['id' => $b['id']]);
			}
		}
	}
}

function twitter_addon_admin_post(App $a)
{
	$consumerkey    = x($_POST, 'consumerkey')    ? notags(trim($_POST['consumerkey']))    : '';
	$consumersecret = x($_POST, 'consumersecret') ? notags(trim($_POST['consumersecret'])) : '';
	Config::set('twitter', 'consumerkey', $consumerkey);
	Config::set('twitter', 'consumersecret', $consumersecret);
	info(L10n::t('Settings updated.') . EOL);
}

function twitter_addon_admin(App $a, &$o)
{
	$t = get_markup_template("admin.tpl", "addon/twitter/");

	$o = replace_macros($t, [
		'$submit' => L10n::t('Save Settings'),
		// name, label, value, help, [extra values]
		'$consumerkey' => ['consumerkey', L10n::t('Consumer key'), Config::get('twitter', 'consumerkey'), ''],
		'$consumersecret' => ['consumersecret', L10n::t('Consumer secret'), Config::get('twitter', 'consumersecret'), ''],
	]);
}

function twitter_cron(App $a, $b)
{
	$last = Config::get('twitter', 'last_poll');

	$poll_interval = intval(Config::get('twitter', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = TWITTER_DEFAULT_POLL_INTERVAL;
	}

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			logger('twitter: poll intervall not reached');
			return;
		}
	}
	logger('twitter: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'mirror_posts' AND `v` = '1'");
	if (count($r)) {
		foreach ($r as $rr) {
			logger('twitter: fetching for user ' . $rr['uid']);
			Worker::add(PRIORITY_MEDIUM, "addon/twitter/twitter_sync.php", 1, (int) $rr['uid']);
		}
	}

	$abandon_days = intval(Config::get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'import' AND `v` = '1'");
	if (count($r)) {
		foreach ($r as $rr) {
			if ($abandon_days != 0) {
				$user = q("SELECT `login_date` FROM `user` WHERE uid=%d AND `login_date` >= '%s'", $rr['uid'], $abandon_limit);
				if (!count($user)) {
					logger('abandoned account: timeline from user ' . $rr['uid'] . ' will not be imported');
					continue;
				}
			}

			logger('twitter: importing timeline from user ' . $rr['uid']);
			Worker::add(PRIORITY_MEDIUM, "addon/twitter/twitter_sync.php", 2, (int) $rr['uid']);
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

	logger('twitter: cron_end');

	Config::set('twitter', 'last_poll', time());
}

function twitter_expire(App $a, $b)
{
	$days = Config::get('twitter', 'expire');

	if ($days == 0) {
		return;
	}

	if (method_exists('dba', 'delete')) {
		$r = dba::select('item', ['id'], ['deleted' => true, 'network' => NETWORK_TWITTER]);
		while ($row = dba::fetch($r)) {
			dba::delete('item', ['id' => $row['id']]);
		}
		dba::close($r);
	} else {
		$r = q("DELETE FROM `item` WHERE `deleted` AND `network` = '%s'", dbesc(NETWORK_TWITTER));
	}

	require_once "include/items.php";

	logger('twitter_expire: expire_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'import' AND `v` = '1' ORDER BY RAND()");
	if (count($r)) {
		foreach ($r as $rr) {
			logger('twitter_expire: user ' . $rr['uid']);
			Item::expire($rr['uid'], $days, NETWORK_TWITTER, true);
		}
	}

	logger('twitter_expire: expire_end');
}

function twitter_prepare_body(App $a, &$b)
{
	if ($b["item"]["network"] != NETWORK_TWITTER) {
		return;
	}

	if ($b["preview"]) {
		$max_char = 280;
		$item = $b["item"];
		$item["plink"] = $a->get_baseurl() . "/display/" . $a->user["nickname"] . "/" . $item["parent"];

		$r = q("SELECT `author-link` FROM item WHERE item.uri = '%s' AND item.uid = %d LIMIT 1",
			dbesc($item["thr-parent"]),
			intval(local_user()));

		if (count($r)) {
			$orig_post = $r[0];

			$nicknameplain = preg_replace("=https?://twitter.com/(.*)=ism", "$1", $orig_post["author-link"]);
			$nickname = "@[url=" . $orig_post["author-link"] . "]" . $nicknameplain . "[/url]";
			$nicknameplain = "@" . $nicknameplain;

			if ((strpos($item["body"], $nickname) === false) && (strpos($item["body"], $nicknameplain) === false)) {
				$item["body"] = $nickname . " " . $item["body"];
			}
		}

		$msgarr = BBCode::toPlaintext($item, $max_char, true, 8);
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
	$datarray["type"] = "wall";
	$datarray["api_source"] = true;
	$datarray["profile_uid"] = $uid;
	$datarray["extid"] = NETWORK_TWITTER;
	$datarray['message_id'] = item_new_uri($a->get_hostname(), $uid, NETWORK_TWITTER . ":" . $post->id);
	$datarray['object'] = json_encode($post);
	$datarray["title"] = "";

	if (is_object($post->retweeted_status)) {
		// We don't support nested shares, so we mustn't show quotes as shares on retweets
		$item = twitter_createpost($a, $uid, $post->retweeted_status, ['id' => 0], false, false, true);

		$datarray['body'] = "\n" . share_header(
			$item['author-name'],
			$item['author-link'],
			$item['author-avatar'],
			"",
			$item['created'],
			$item['plink']
		);

		$datarray['body'] .= $item['body'] . '[/share]';
	} else {
		$item = twitter_createpost($a, $uid, $post, ['id' => 0], false, false, false);

		$datarray['body'] = $item['body'];
	}

	$datarray["source"] = $item['app'];
	$datarray["verb"] = $item['verb'];

	if (isset($item["location"])) {
		$datarray["location"] = $item["location"];
	}

	if (isset($item["coord"])) {
		$datarray["coord"] = $item["coord"];
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
		$application_name = $a->get_hostname();
	}

	$has_picture = false;

	require_once 'mod/item.php';
	require_once 'include/items.php';
	require_once 'mod/share.php';

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

	$parameters = ["exclude_replies" => true, "trim_user" => false, "contributor_details" => true, "include_rts" => true, "tweet_mode" => "extended"];

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	$items = $connection->get('statuses/user_timeline', $parameters);

	if (!is_array($items)) {
		return;
	}

	$posts = array_reverse($items);

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

				$_REQUEST = twitter_do_mirrorpost($a, $uid, $post);

				logger('twitter: posting for user ' . $uid);

				item_post($a);
			}
		}
	}
	PConfig::set($uid, 'twitter', 'lastid', $lastid);
}

function twitter_queue_hook(App $a, &$b)
{
	$qi = q("SELECT * FROM `queue` WHERE `network` = '%s'",
		dbesc(NETWORK_TWITTER)
	);
	if (!count($qi)) {
		return;
	}

	foreach ($qi as $x) {
		if ($x['network'] !== NETWORK_TWITTER) {
			continue;
		}

		logger('twitter_queue: run');

		$r = q("SELECT `user`.* FROM `user` LEFT JOIN `contact` on `contact`.`uid` = `user`.`uid`
			WHERE `contact`.`self` = 1 AND `contact`.`id` = %d LIMIT 1",
			intval($x['cid'])
		);
		if (!count($r)) {
			continue;
		}

		$user = $r[0];

		$ckey    = Config::get('twitter', 'consumerkey');
		$csecret = Config::get('twitter', 'consumersecret');
		$otoken  = PConfig::get($user['uid'], 'twitter', 'oauthtoken');
		$osecret = PConfig::get($user['uid'], 'twitter', 'oauthsecret');

		$success = false;

		if ($ckey && $csecret && $otoken && $osecret) {
			logger('twitter_queue: able to post');

			$z = unserialize($x['content']);

			$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
			$result = $connection->post($z['url'], $z['post']);

			logger('twitter_queue: post result: ' . print_r($result, true), LOGGER_DEBUG);

			if ($result->errors) {
				logger('twitter_queue: Send to Twitter failed: "' . print_r($result->errors, true) . '"');
			} else {
				$success = true;
				Queue::removeItem($x['id']);
			}
		} else {
			logger("twitter_queue: Error getting tokens for user " . $user['uid']);
		}

		if (!$success) {
			logger('twitter_queue: delayed');
			Queue::updateTime($x['id']);
		}
	}
}

function twitter_fix_avatar($avatar)
{
	$new_avatar = str_replace("_normal.", ".", $avatar);

	$info = Image::getInfoFromURL($new_avatar);
	if (!$info) {
		$new_avatar = $avatar;
	}

	return $new_avatar;
}

function twitter_fetch_contact($uid, $contact, $create_user)
{
	if ($contact->id_str == "") {
		return -1;
	}

	$avatar = twitter_fix_avatar($contact->profile_image_url_https);

	GContact::update(["url" => "https://twitter.com/" . $contact->screen_name,
		"network" => NETWORK_TWITTER, "photo" => $avatar, "hide" => true,
		"name" => $contact->name, "nick" => $contact->screen_name,
		"location" => $contact->location, "about" => $contact->description,
		"addr" => $contact->screen_name . "@twitter.com", "generation" => 2]);

	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
		intval($uid),
		dbesc("twitter::" . $contact->id_str));

	if (!count($r) && !$create_user) {
		return 0;
	}

	if (count($r) && ($r[0]["readonly"] || $r[0]["blocked"])) {
		logger("twitter_fetch_contact: Contact '" . $r[0]["nick"] . "' is blocked or readonly.", LOGGER_DEBUG);
		return -1;
	}

	if (!count($r)) {
		// create contact record
		q("INSERT INTO `contact` (`uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
					`name`, `nick`, `photo`, `network`, `rel`, `priority`,
					`location`, `about`, `writable`, `blocked`, `readonly`, `pending`)
					VALUES (%d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, '%s', '%s', %d, 0, 0, 0)",
			intval($uid),
			dbesc(DateTimeFormat::utcNow()),
			dbesc("https://twitter.com/" . $contact->screen_name),
			dbesc(normalise_link("https://twitter.com/" . $contact->screen_name)),
			dbesc($contact->screen_name."@twitter.com"),
			dbesc("twitter::" . $contact->id_str),
			dbesc(''),
			dbesc("twitter::" . $contact->id_str),
			dbesc($contact->name),
			dbesc($contact->screen_name),
			dbesc($avatar),
			dbesc(NETWORK_TWITTER),
			intval(CONTACT_IS_FRIEND),
			intval(1),
			dbesc($contact->location),
			dbesc($contact->description),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d LIMIT 1",
			dbesc("twitter::".$contact->id_str),
			intval($uid)
		);

		if (!count($r)) {
			return false;
		}

		$contact_id = $r[0]['id'];

		Group::addMember(User::getDefaultGroup($uid), $contact_id);

		$photos = Photo::importProfilePhoto($avatar, $uid, $contact_id, true);

		if ($photos) {
			q("UPDATE `contact` SET `photo` = '%s',
						`thumb` = '%s',
						`micro` = '%s',
						`name-date` = '%s',
						`uri-date` = '%s',
							`avatar-date` = '%s'
					WHERE `id` = %d",
				dbesc($photos[0]),
				dbesc($photos[1]),
				dbesc($photos[2]),
				dbesc(DateTimeFormat::utcNow()),
				dbesc(DateTimeFormat::utcNow()),
				dbesc(DateTimeFormat::utcNow()),
				intval($contact_id)
			);
		}
	} else {
		// update profile photos once every two weeks as we have no notification of when they change.
		//$update_photo = (($r[0]['avatar-date'] < DateTimeFormat::convert('now -2 days', '', '', )) ? true : false);
		$update_photo = ($r[0]['avatar-date'] < DateTimeFormat::utc('now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion
		if ((!$r[0]['photo']) || (!$r[0]['thumb']) || (!$r[0]['micro']) || ($update_photo)) {
			logger("twitter_fetch_contact: Updating contact " . $contact->screen_name, LOGGER_DEBUG);

			$photos = Photo::importProfilePhoto($avatar, $uid, $r[0]['id'], true);

			if ($photos) {
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
					dbesc($photos[0]),
					dbesc($photos[1]),
					dbesc($photos[2]),
					dbesc(DateTimeFormat::utcNow()),
					dbesc(DateTimeFormat::utcNow()),
					dbesc(DateTimeFormat::utcNow()),
					dbesc("https://twitter.com/".$contact->screen_name),
					dbesc(normalise_link("https://twitter.com/".$contact->screen_name)),
					dbesc($contact->screen_name."@twitter.com"),
					dbesc($contact->name),
					dbesc($contact->screen_name),
					dbesc($contact->location),
					dbesc($contact->description),
					intval($r[0]['id'])
				);
			}
		}
	}

	return $r[0]["id"];
}

function twitter_fetchuser(App $a, $uid, $screen_name = "", $user_id = "")
{
	$ckey = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if (count($r)) {
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
	$user = $connection->get('users/show', $parameters);

	if (!is_object($user)) {
		return;
	}

	$contact_id = twitter_fetch_contact($uid, $user, true);

	return $contact_id;
}

function twitter_expand_entities(App $a, $body, $item, $no_tags = false, $picture)
{
	$tags = "";

	$plain = $body;

	if (isset($item->entities->urls)) {
		$type = "";
		$footerurl = "";
		$footerlink = "";
		$footer = "";

		foreach ($item->entities->urls as $url) {
			$plain = str_replace($url->url, '', $plain);

			if ($url->url && $url->expanded_url && $url->display_url) {
				$expanded_url = Network::finalUrl($url->expanded_url);

				$oembed_data = OEmbed::fetchURL($expanded_url);

				// Quickfix: Workaround for URL with "[" and "]" in it
				if (strpos($expanded_url, "[") || strpos($expanded_url, "]")) {
					$expanded_url = $url->url;
				}

				if ($type == "") {
					$type = $oembed_data->type;
				}

				if ($oembed_data->type == "video") {
					//$body = str_replace($url->url,
					//		"[video]".$expanded_url."[/video]", $body);
					//$dontincludemedia = true;
					$type = $oembed_data->type;
					$footerurl = $expanded_url;
					$footerlink = "[url=" . $expanded_url . "]" . $expanded_url . "[/url]";

					$body = str_replace($url->url, $footerlink, $body);
					//} elseif (($oembed_data->type == "photo") AND isset($oembed_data->url) AND !$dontincludemedia) {
				} elseif (($oembed_data->type == "photo") && isset($oembed_data->url)) {
					$body = str_replace($url->url, "[url=" . $expanded_url . "][img]" . $oembed_data->url . "[/img][/url]", $body);
					//$dontincludemedia = true;
				} elseif ($oembed_data->type != "link") {
					$body = str_replace($url->url, "[url=" . $expanded_url . "]" . $expanded_url . "[/url]", $body);
				} else {
					$img_str = Network::fetchUrl($expanded_url, true, $redirects, 4);

					$tempfile = tempnam(get_temppath(), "cache");
					file_put_contents($tempfile, $img_str);
					$mime = image_type_to_mime_type(exif_imagetype($tempfile));
					unlink($tempfile);

					if (substr($mime, 0, 6) == "image/") {
						$type = "photo";
						$body = str_replace($url->url, "[img]" . $expanded_url . "[/img]", $body);
						//$dontincludemedia = true;
					} else {
						$type = $oembed_data->type;
						$footerurl = $expanded_url;
						$footerlink = "[url=" . $expanded_url . "]" . $expanded_url . "[/url]";

						$body = str_replace($url->url, $footerlink, $body);
					}
				}
			}
		}

		if ($footerurl != "") {
			$footer = add_page_info($footerurl, false, $picture);
		}

		if (($footerlink != "") && (trim($footer) != "")) {
			$removedlink = trim(str_replace($footerlink, "", $body));

			if (($removedlink == "") || strstr($body, $removedlink)) {
				$body = $removedlink;
			}

			$body .= $footer;
		}

		if (($footer == "") && ($picture != "")) {
			$body .= "\n\n[img]" . $picture . "[/img]\n";
		} elseif (($footer == "") && ($picture == "")) {
			$body = add_page_info_to_body($body);
		}

		if ($no_tags) {
			return ["body" => $body, "tags" => "", "plain" => $plain];
		}

		$tags_arr = [];

		foreach ($item->entities->hashtags AS $hashtag) {
			$url = "#[url=" . $a->get_baseurl() . "/search?tag=" . rawurlencode($hashtag->text) . "]" . $hashtag->text . "[/url]";
			$tags_arr["#" . $hashtag->text] = $url;
			$body = str_replace("#" . $hashtag->text, $url, $body);
		}

		foreach ($item->entities->user_mentions AS $mention) {
			$url = "@[url=https://twitter.com/" . rawurlencode($mention->screen_name) . "]" . $mention->screen_name . "[/url]";
			$tags_arr["@" . $mention->screen_name] = $url;
			$body = str_replace("@" . $mention->screen_name, $url, $body);
		}

		// it seems as if the entities aren't always covering all mentions. So the rest will be checked here
		$tags = get_tags($body);

		if (count($tags)) {
			foreach ($tags as $tag) {
				if (strstr(trim($tag), " ")) {
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
					$url = '#[url=' . $a->get_baseurl() . '/search?tag=' . rawurlencode($basetag) . ']' . $basetag . '[/url]';
					$body = str_replace($tag, $url, $body);
					$tags_arr["#" . $basetag] = $url;
				} elseif (strpos($tag, '@') === 0) {
					if (strpos($tag, '[url=')) {
						continue;
					}

					$basetag = substr($tag, 1);
					$url = '@[url=https://twitter.com/' . rawurlencode($basetag) . ']' . $basetag . '[/url]';
					$body = str_replace($tag, $url, $body);
					$tags_arr["@" . $basetag] = $url;
				}
			}
		}

		$tags = implode($tags_arr, ",");
	}
	return ["body" => $body, "tags" => $tags, "plain" => $plain];
}

/**
 * @brief Fetch media entities and add media links to the body
 *
 * @param object $post Twitter object with the post
 * @param array $postarray Array of the item that is about to be posted
 *
 * @return $picture string Returns a a single picture string if it isn't a media post
 */
function twitter_media_entities($post, &$postarray)
{
	// There are no media entities? So we quit.
	if (!is_array($post->extended_entities->media)) {
		return "";
	}

	// When the post links to an external page, we only take one picture.
	// We only do this when there is exactly one media.
	if ((count($post->entities->urls) > 0) && (count($post->extended_entities->media) == 1)) {
		$picture = "";
		foreach ($post->extended_entities->media AS $medium) {
			if (isset($medium->media_url_https)) {
				$picture = $medium->media_url_https;
				$postarray['body'] = str_replace($medium->url, "", $postarray['body']);
			}
		}
		return $picture;
	}

	// This is a pure media post, first search for all media urls
	$media = [];
	foreach ($post->extended_entities->media AS $medium) {
		switch ($medium->type) {
			case 'photo':
				$media[$medium->url] .= "\n[img]" . $medium->media_url_https . "[/img]";
				$postarray['object-type'] = ACTIVITY_OBJ_IMAGE;
				break;
			case 'video':
			case 'animated_gif':
				$media[$medium->url] .= "\n[img]" . $medium->media_url_https . "[/img]";
				$postarray['object-type'] = ACTIVITY_OBJ_VIDEO;
				if (is_array($medium->video_info->variants)) {
					$bitrate = 0;
					// We take the video with the highest bitrate
					foreach ($medium->video_info->variants AS $variant) {
						if (($variant->content_type == "video/mp4") && ($variant->bitrate >= $bitrate)) {
							$media[$medium->url] = "\n[video]" . $variant->url . "[/video]";
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
	return "";
}

function twitter_createpost(App $a, $uid, $post, $self, $create_user, $only_existing_contact, $noquote)
{
	$postarray = [];
	$postarray['network'] = NETWORK_TWITTER;
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['uri'] = "twitter::" . $post->id_str;
	$postarray['object'] = json_encode($post);

	// Don't import our own comments
	$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
		dbesc($postarray['uri']),
		intval($uid)
	);

	if (count($r)) {
		logger("Item with extid " . $postarray['uri'] . " found.", LOGGER_DEBUG);
		return [];
	}

	$contactid = 0;

	if ($post->in_reply_to_status_id_str != "") {
		$parent = "twitter::" . $post->in_reply_to_status_id_str;

		$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
			dbesc($parent),
			intval($uid)
		);
		if (count($r)) {
			$postarray['thr-parent'] = $r[0]["uri"];
			$postarray['parent-uri'] = $r[0]["parent-uri"];
			$postarray['parent'] = $r[0]["parent"];
			$postarray['object-type'] = ACTIVITY_OBJ_COMMENT;
		} else {
			$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($parent),
				intval($uid)
			);
			if (count($r)) {
				$postarray['thr-parent'] = $r[0]['uri'];
				$postarray['parent-uri'] = $r[0]['parent-uri'];
				$postarray['parent'] = $r[0]['parent'];
				$postarray['object-type'] = ACTIVITY_OBJ_COMMENT;
			} else {
				$postarray['thr-parent'] = $postarray['uri'];
				$postarray['parent-uri'] = $postarray['uri'];
				$postarray['object-type'] = ACTIVITY_OBJ_NOTE;
			}
		}

		// Is it me?
		$own_id = PConfig::get($uid, 'twitter', 'own_id');

		if ($post->user->id_str == $own_id) {
			$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
				intval($uid));

			if (count($r)) {
				$contactid = $r[0]["id"];

				$postarray['owner-name']   = $r[0]["name"];
				$postarray['owner-link']   = $r[0]["url"];
				$postarray['owner-avatar'] = $r[0]["photo"];
			} else {
				logger("No self contact for user " . $uid, LOGGER_DEBUG);
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
		$contactid = twitter_fetch_contact($uid, $post->user, $create_user);

		$postarray['owner-name'] = $post->user->name;
		$postarray['owner-link'] = "https://twitter.com/" . $post->user->screen_name;
		$postarray['owner-avatar'] = twitter_fix_avatar($post->user->profile_image_url_https);
	}

	if (($contactid == 0) && !$only_existing_contact) {
		$contactid = $self['id'];
	} elseif ($contactid <= 0) {
		logger("Contact ID is zero or less than zero.", LOGGER_DEBUG);
		return [];
	}

	$postarray['contact-id'] = $contactid;

	$postarray['verb'] = ACTIVITY_POST;
	$postarray['author-name'] = $postarray['owner-name'];
	$postarray['author-link'] = $postarray['owner-link'];
	$postarray['author-avatar'] = $postarray['owner-avatar'];
	$postarray['plink'] = "https://twitter.com/" . $post->user->screen_name . "/status/" . $post->id_str;
	$postarray['app'] = strip_tags($post->source);

	if ($post->user->protected) {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self['id'] . '>';
	}

	if (is_string($post->full_text)) {
		$postarray['body'] = $post->full_text;
	} else {
		$postarray['body'] = $post->text;
	}

	// When the post contains links then use the correct object type
	if (count($post->entities->urls) > 0) {
		$postarray['object-type'] = ACTIVITY_OBJ_BOOKMARK;
	}

	// Search for media links
	$picture = twitter_media_entities($post, $postarray);

	$converted = twitter_expand_entities($a, $postarray['body'], $post, false, $picture);
	$postarray['body'] = $converted["body"];
	$postarray['tag'] = $converted["tags"];
	$postarray['created'] = DateTimeFormat::utc($post->created_at);
	$postarray['edited'] = DateTimeFormat::utc($post->created_at);

	$statustext = $converted["plain"];

	if (is_string($post->place->name)) {
		$postarray["location"] = $post->place->name;
	}
	if (is_string($post->place->full_name)) {
		$postarray["location"] = $post->place->full_name;
	}
	if (is_array($post->geo->coordinates)) {
		$postarray["coord"] = $post->geo->coordinates[0] . " " . $post->geo->coordinates[1];
	}
	if (is_array($post->coordinates->coordinates)) {
		$postarray["coord"] = $post->coordinates->coordinates[1] . " " . $post->coordinates->coordinates[0];
	}
	if (is_object($post->retweeted_status)) {
		$retweet = twitter_createpost($a, $uid, $post->retweeted_status, $self, false, false, $noquote);

		$retweet['object'] = $postarray['object'];
		$retweet['private'] = $postarray['private'];
		$retweet['allow_cid'] = $postarray['allow_cid'];
		$retweet['contact-id'] = $postarray['contact-id'];
		$retweet['owner-name'] = $postarray['owner-name'];
		$retweet['owner-link'] = $postarray['owner-link'];
		$retweet['owner-avatar'] = $postarray['owner-avatar'];

		$postarray = $retweet;
	}

	if (is_object($post->quoted_status) && !$noquote) {
		$quoted = twitter_createpost($a, $uid, $post->quoted_status, $self, false, false, true);

		$postarray['body'] = $statustext;

		$postarray['body'] .= "\n" . share_header(
			$quoted['author-name'],
			$quoted['author-link'],
			$quoted['author-avatar'],
			"",
			$quoted['created'],
			$quoted['plink']
		);

		$postarray['body'] .= $quoted['body'] . '[/share]';
	}

	return $postarray;
}

function twitter_checknotification(App $a, $uid, $own_id, $top_item, $postarray)
{
	/// TODO: this whole function doesn't seem to work. Needs complete check
	$user = q("SELECT * FROM `contact` WHERE `uid` = %d AND `self` LIMIT 1",
		intval($uid)
	);

	if (!count($user)) {
		return;
	}

	// Is it me?
	if (link_compare($user[0]["url"], $postarray['author-link'])) {
		return;
	}

	$own_user = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
		intval($uid),
		dbesc("twitter::".$own_id)
	);

	if (!count($own_user)) {
		return;
	}

	// Is it me from twitter?
	if (link_compare($own_user[0]["url"], $postarray['author-link'])) {
		return;
	}

	$myconv = q("SELECT `author-link`, `author-avatar`, `parent` FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `parent` != 0 AND `deleted` = 0",
		dbesc($postarray['parent-uri']),
		intval($uid)
	);

	if (count($myconv)) {
		foreach ($myconv as $conv) {
			// now if we find a match, it means we're in this conversation
			if (!link_compare($conv['author-link'], $user[0]["url"]) && !link_compare($conv['author-link'], $own_user[0]["url"])) {
				continue;
			}

			require_once 'include/enotify.php';

			$conv_parent = $conv['parent'];

			notification([
				'type' => NOTIFY_COMMENT,
				'notify_flags' => $user[0]['notify-flags'],
				'language' => $user[0]['language'],
				'to_name' => $user[0]['username'],
				'to_email' => $user[0]['email'],
				'uid' => $user[0]['uid'],
				'item' => $postarray,
				'link' => $a->get_baseurl() . '/display/' . urlencode(Item::getGuidById($top_item)),
				'source_name' => $postarray['author-name'],
				'source_link' => $postarray['author-link'],
				'source_photo' => $postarray['author-avatar'],
				'verb' => ACTIVITY_POST,
				'otype' => 'item',
				'parent' => $conv_parent,
			]);

			// only send one notification
			break;
		}
	}
}

function twitter_fetchparentposts(App $a, $uid, $post, $connection, $self, $own_id)
{
	logger("twitter_fetchparentposts: Fetching for user " . $uid . " and post " . $post->id_str, LOGGER_DEBUG);

	$posts = [];

	while ($post->in_reply_to_status_id_str != "") {
		$parameters = ["trim_user" => false, "tweet_mode" => "extended", "id" => $post->in_reply_to_status_id_str];

		$post = $connection->get('statuses/show', $parameters);

		if (!count($post)) {
			logger("twitter_fetchparentposts: Can't fetch post " . $parameters->id, LOGGER_DEBUG);
			break;
		}

		$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
			dbesc("twitter::".$post->id_str),
			intval($uid)
		);

		if (count($r)) {
			break;
		}

		$posts[] = $post;
	}

	logger("twitter_fetchparentposts: Fetching " . count($posts) . " parents", LOGGER_DEBUG);

	$posts = array_reverse($posts);

	if (count($posts)) {
		foreach ($posts as $post) {
			$postarray = twitter_createpost($a, $uid, $post, $self, false, false, false);

			if (trim($postarray['body']) == "") {
				continue;
			}

			$item = Item::insert($postarray);
			$postarray["id"] = $item;

			logger('twitter_fetchparentpost: User ' . $self["nick"] . ' posted parent timeline item ' . $item);

			if ($item && !function_exists("check_item_notification")) {
				twitter_checknotification($a, $uid, $own_id, $item, $postarray);
			}
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

	logger("twitter_fetchhometimeline: Fetching for user " . $uid, LOGGER_DEBUG);

	$application_name = Config::get('twitter', 'application_name');

	if ($application_name == "") {
		$application_name = $a->get_hostname();
	}

	require_once 'include/items.php';

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

	$own_contact = twitter_fetch_own_contact($a, $uid);

	$r = q("SELECT * FROM `contact` WHERE `id` = %d AND `uid` = %d LIMIT 1",
		intval($own_contact),
		intval($uid));

	if (count($r)) {
		$own_id = $r[0]["nick"];
	} else {
		logger("twitter_fetchhometimeline: Own twitter contact not found for user " . $uid, LOGGER_DEBUG);
		return;
	}

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if (count($r)) {
		$self = $r[0];
	} else {
		logger("twitter_fetchhometimeline: Own contact not found for user " . $uid, LOGGER_DEBUG);
		return;
	}

	$u = q("SELECT * FROM user WHERE uid = %d LIMIT 1",
		intval($uid));
	if (!count($u)) {
		logger("twitter_fetchhometimeline: Own user not found for user " . $uid, LOGGER_DEBUG);
		return;
	}

	$parameters = ["exclude_replies" => false, "trim_user" => false, "contributor_details" => true, "include_rts" => true, "tweet_mode" => "extended"];
	//$parameters["count"] = 200;
	// Fetching timeline
	$lastid = PConfig::get($uid, 'twitter', 'lasthometimelineid');

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	$items = $connection->get('statuses/home_timeline', $parameters);

	if (!is_array($items)) {
		logger("twitter_fetchhometimeline: Error fetching home timeline: " . print_r($items, true), LOGGER_DEBUG);
		return;
	}

	$posts = array_reverse($items);

	logger("twitter_fetchhometimeline: Fetching timeline for user " . $uid . " " . sizeof($posts) . " items", LOGGER_DEBUG);

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
				logger("twitter_fetchhometimeline: Skip previously sended post", LOGGER_DEBUG);
				continue;
			}

			if ($mirror_posts && $post->user->screen_name == $own_id && $post->in_reply_to_status_id_str == "") {
				logger("twitter_fetchhometimeline: Skip post that will be mirrored", LOGGER_DEBUG);
				continue;
			}

			if ($post->in_reply_to_status_id_str != "") {
				twitter_fetchparentposts($a, $uid, $post, $connection, $self, $own_id);
			}

			$postarray = twitter_createpost($a, $uid, $post, $self, $create_user, true, false);

			if (trim($postarray['body']) == "") {
				continue;
			}

			$item = Item::insert($postarray);
			$postarray["id"] = $item;

			logger('twitter_fetchhometimeline: User ' . $self["nick"] . ' posted home timeline item ' . $item);

			if ($item && !function_exists("check_item_notification")) {
				twitter_checknotification($a, $uid, $own_id, $item, $postarray);
			}
		}
	}
	PConfig::set($uid, 'twitter', 'lasthometimelineid', $lastid);

	// Fetching mentions
	$lastid = PConfig::get($uid, 'twitter', 'lastmentionid');

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	$items = $connection->get('statuses/mentions_timeline', $parameters);

	if (!is_array($items)) {
		logger("twitter_fetchhometimeline: Error fetching mentions: " . print_r($items, true), LOGGER_DEBUG);
		return;
	}

	$posts = array_reverse($items);

	logger("twitter_fetchhometimeline: Fetching mentions for user " . $uid . " " . sizeof($posts) . " items", LOGGER_DEBUG);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid) {
				$lastid = $post->id_str;
			}

			if ($first_time) {
				continue;
			}

			if ($post->in_reply_to_status_id_str != "") {
				twitter_fetchparentposts($a, $uid, $post, $connection, $self, $own_id);
			}

			$postarray = twitter_createpost($a, $uid, $post, $self, false, false, false);

			if (trim($postarray['body']) == "") {
				continue;
			}

			$item = Item::insert($postarray);
			$postarray["id"] = $item;

			if ($item && function_exists("check_item_notification")) {
				check_item_notification($item, $uid, NOTIFY_TAGSELF);
			}

			if (!isset($postarray["parent"]) || ($postarray["parent"] == 0)) {
				$postarray["parent"] = $item;
			}

			logger('twitter_fetchhometimeline: User ' . $self["nick"] . ' posted mention timeline item ' . $item);

			if ($item == 0) {
				$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($postarray['uri']),
					intval($uid)
				);
				if (count($r)) {
					$item = $r[0]['id'];
					$parent_id = $r[0]['parent'];
				}
			} else {
				$parent_id = $postarray['parent'];
			}

			if (($item != 0) && !function_exists("check_item_notification")) {
				require_once 'include/enotify.php';
				notification([
					'type'         => NOTIFY_TAGSELF,
					'notify_flags' => $u[0]['notify-flags'],
					'language'     => $u[0]['language'],
					'to_name'      => $u[0]['username'],
					'to_email'     => $u[0]['email'],
					'uid'          => $u[0]['uid'],
					'item'         => $postarray,
					'link'         => $a->get_baseurl() . '/display/' . urlencode(Item::getGuidById($item)),
					'source_name'  => $postarray['author-name'],
					'source_link'  => $postarray['author-link'],
					'source_photo' => $postarray['author-avatar'],
					'verb'         => ACTIVITY_TAG,
					'otype'        => 'item',
					'parent'       => $parent_id
				]);
			}
		}
	}

	PConfig::set($uid, 'twitter', 'lastmentionid', $lastid);
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
		$user = $connection->get('account/verify_credentials');

		PConfig::set($uid, 'twitter', 'own_id', $user->id_str);

		$contact_id = twitter_fetch_contact($uid, $user, true);
	} else {
		$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
			intval($uid),
			dbesc("twitter::" . $own_id));
		if (count($r)) {
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
	if ($matches[1] != "") {
		$link = $matches[1];
	}

	preg_match('/link="(.*?)"/ism', $attributes, $matches);
	if ($matches[1] != "") {
		$link = $matches[1];
	}

	$id = preg_replace("=https?://twitter.com/(.*)/status/(.*)=ism", "$2", $link);
	if ($id == $link) {
		return false;
	}

	logger('twitter_is_retweet: Retweeting id ' . $id . ' for user ' . $uid, LOGGER_DEBUG);

	$ckey    = Config::get('twitter', 'consumerkey');
	$csecret = Config::get('twitter', 'consumersecret');
	$otoken  = PConfig::get($uid, 'twitter', 'oauthtoken');
	$osecret = PConfig::get($uid, 'twitter', 'oauthsecret');

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
	$result = $connection->post('statuses/retweet/' . $id);

	logger('twitter_is_retweet: result ' . print_r($result, true), LOGGER_DEBUG);

	return !isset($result->errors);
}
