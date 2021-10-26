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
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Conversation;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\ItemURI;
use Friendica\Model\Post;
use Friendica\Model\Tag;
use Friendica\Model\User;
use Friendica\Protocol\Activity;
use Friendica\Core\Config\Util\ConfigFileLoader;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Images;
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
	Hook::register('support_follow'         , __FILE__, 'twitter_support_follow');
	Hook::register('follow'                 , __FILE__, 'twitter_follow');
	Hook::register('unfollow'               , __FILE__, 'twitter_unfollow');
	Hook::register('block'                  , __FILE__, 'twitter_block');
	Hook::register('unblock'                , __FILE__, 'twitter_unblock');
	Hook::register('expire'                 , __FILE__, 'twitter_expire');
	Hook::register('prepare_body'           , __FILE__, 'twitter_prepare_body');
	Hook::register('check_item_notification', __FILE__, 'twitter_check_item_notification');
	Hook::register('probe_detect'           , __FILE__, 'twitter_probe_detect');
	Hook::register('parse_link'             , __FILE__, 'twitter_parse_link');
	Logger::info("installed twitter");
}

// Hook functions

function twitter_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('twitter'));
}

function twitter_check_item_notification(App $a, array &$notification_data)
{
	$own_id = DI::pConfig()->get($notification_data['uid'], 'twitter', 'own_id');

	$own_user = Contact::selectFirst(['url'], ['uid' => $notification_data['uid'], 'alias' => 'twitter::'.$own_id]);
	if ($own_user) {
		$notification_data['profiles'][] = $own_user['url'];
	}
}

function twitter_support_follow(App $a, array &$data)
{
	if ($data['protocol'] == Protocol::TWITTER) {
		$data['result'] = true;
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

	$uid = $a->getLoggedInUserId();

	twitter_api_contact('friendships/create', ['network' => Protocol::TWITTER, 'nick' => $nickname], $uid);

	$user = twitter_fetchuser($nickname);

	$contact_id = twitter_fetch_contact($uid, $user, true);

	$contact = Contact::getById($contact_id, ['name', 'nick', 'url', 'addr', 'batch', 'notify', 'poll', 'request', 'confirm', 'poco', 'photo', 'priority', 'network', 'alias', 'pubkey']);

	if (DBA::isResult($contact)) {
		$contact["contact"] = $contact;
	}
}

function twitter_unfollow(App $a, array &$hook_data)
{
	$hook_data['result'] = twitter_api_contact('friendships/destroy', $hook_data['contact'], $hook_data['contact']['uid']);
}

function twitter_block(App $a, array &$hook_data)
{
	$hook_data['result'] = twitter_api_contact('blocks/create', $hook_data['contact'], $hook_data['uid']);

	if ($hook_data['result'] === true) {
		Contact::removeFollower($hook_data['contact']);
		Contact::unfollow($hook_data['contact']['id'], $hook_data['uid']);
	}
}

function twitter_unblock(App $a, array &$hook_data)
{
	$hook_data['result'] = twitter_api_contact('blocks/destroy', $hook_data['contact'], $hook_data['uid']);
}

function twitter_api_contact(string $apiPath, array $contact, int $uid): ?bool
{
	if ($contact['network'] !== Protocol::TWITTER) {
		return null;
	}

	return twitter_api_call($uid, $apiPath, ['screen_name' => $contact['nick']]);
}

function twitter_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (DI::pConfig()->get(local_user(), 'twitter', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'twitter_enable',
				DI::l10n()->t('Post to Twitter'),
				DI::pConfig()->get(local_user(), 'twitter', 'post_by_default')
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
		DI::pConfig()->delete(local_user(), 'twitter', 'consumerkey');
		DI::pConfig()->delete(local_user(), 'twitter', 'consumersecret');
		DI::pConfig()->delete(local_user(), 'twitter', 'oauthtoken');
		DI::pConfig()->delete(local_user(), 'twitter', 'oauthsecret');
		DI::pConfig()->delete(local_user(), 'twitter', 'post');
		DI::pConfig()->delete(local_user(), 'twitter', 'post_by_default');
		DI::pConfig()->delete(local_user(), 'twitter', 'lastid');
		DI::pConfig()->delete(local_user(), 'twitter', 'mirror_posts');
		DI::pConfig()->delete(local_user(), 'twitter', 'import');
		DI::pConfig()->delete(local_user(), 'twitter', 'create_user');
		DI::pConfig()->delete(local_user(), 'twitter', 'own_id');
	} else {
		if (isset($_POST['twitter-pin'])) {
			//  if the user supplied us with a PIN from Twitter, let the magic of OAuth happen
			Logger::notice('got a Twitter PIN');
			$ckey    = DI::config()->get('twitter', 'consumerkey');
			$csecret = DI::config()->get('twitter', 'consumersecret');
			//  the token and secret for which the PIN was generated were hidden in the settings
			//  form as token and token2, we need a new connection to Twitter using these token
			//  and secret to request a Access Token with the PIN
			try {
				if (empty($_POST['twitter-pin'])) {
					throw new Exception(DI::l10n()->t('You submitted an empty PIN, please Sign In with Twitter again to get a new one.'));
				}

				$connection = new TwitterOAuth($ckey, $csecret, $_POST['twitter-token'], $_POST['twitter-token2']);
				$token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_POST['twitter-pin']]);
				//  ok, now that we have the Access Token, save them in the user config
				DI::pConfig()->set(local_user(), 'twitter', 'oauthtoken', $token['oauth_token']);
				DI::pConfig()->set(local_user(), 'twitter', 'oauthsecret', $token['oauth_token_secret']);
				DI::pConfig()->set(local_user(), 'twitter', 'post', 1);
			} catch(Exception $e) {
				notice($e->getMessage());
			} catch(TwitterOAuthException $e) {
				notice($e->getMessage());
			}
			//  reload the Addon Settings page, if we don't do it see Bug #42
			DI::baseUrl()->redirect('settings/connectors');
		} else {
			//  if no PIN is supplied in the POST variables, the user has changed the setting
			//  to post a tweet for every new __public__ posting to the wall
			DI::pConfig()->set(local_user(), 'twitter', 'post', intval($_POST['twitter-enable']));
			DI::pConfig()->set(local_user(), 'twitter', 'post_by_default', intval($_POST['twitter-default']));
			DI::pConfig()->set(local_user(), 'twitter', 'mirror_posts', intval($_POST['twitter-mirror']));
			DI::pConfig()->set(local_user(), 'twitter', 'import', intval($_POST['twitter-import']));
			DI::pConfig()->set(local_user(), 'twitter', 'create_user', intval($_POST['twitter-create_user']));

			if (!intval($_POST['twitter-mirror'])) {
				DI::pConfig()->delete(local_user(), 'twitter', 'lastid');
			}
		}
	}
}

function twitter_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$user = User::getById(local_user());

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/twitter/twitter.css' . '" media="all" />' . "\r\n";
	/*	 * *
	 * 1) Check that we have global consumer key & secret
	 * 2) If no OAuthtoken & stuff is present, generate button to get some
	 * 3) Checkbox for "Send public notices (280 chars only)
	 */
	$ckey    = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');
	$otoken  = DI::pConfig()->get(local_user(), 'twitter', 'oauthtoken');
	$osecret = DI::pConfig()->get(local_user(), 'twitter', 'oauthsecret');

	$enabled            = intval(DI::pConfig()->get(local_user(), 'twitter', 'post'));
	$defenabled         = intval(DI::pConfig()->get(local_user(), 'twitter', 'post_by_default'));
	$mirrorenabled      = intval(DI::pConfig()->get(local_user(), 'twitter', 'mirror_posts'));
	$importenabled      = intval(DI::pConfig()->get(local_user(), 'twitter', 'import'));
	$create_userenabled = intval(DI::pConfig()->get(local_user(), 'twitter', 'create_user'));

	$css = (($enabled) ? '' : '-disabled');

	$s .= '<span id="settings_twitter_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_twitter_expanded\'); openClose(\'settings_twitter_inflated\');">';
	$s .= '<img class="connector' . $css . '" src="images/twitter.png" /><h3 class="connector">' . DI::l10n()->t('Twitter Import/Export/Mirror') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_twitter_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_twitter_expanded\'); openClose(\'settings_twitter_inflated\');">';
	$s .= '<img class="connector' . $css . '" src="images/twitter.png" /><h3 class="connector">' . DI::l10n()->t('Twitter Import/Export/Mirror') . '</h3>';
	$s .= '</span>';

	if ((!$ckey) && (!$csecret)) {
		/* no global consumer keys
		 * display warning and skip personal config
		 */
		$s .= '<p>' . DI::l10n()->t('No consumer key pair for Twitter found. Please contact your site administrator.') . '</p>';
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
				$s .= '<p>' . DI::l10n()->t('At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.') . '</p>';
				$s .= '<a href="' . $connection->url('oauth/authorize', ['oauth_token' => $result['oauth_token']]) . '" target="_twitter"><img src="addon/twitter/lighter.png" alt="' . DI::l10n()->t('Log in with Twitter') . '"></a>';
				$s .= '<div id="twitter-pin-wrapper">';
				$s .= '<label id="twitter-pin-label" for="twitter-pin">' . DI::l10n()->t('Copy the PIN from Twitter here') . '</label>';
				$s .= '<input id="twitter-pin" type="text" name="twitter-pin" />';
				$s .= '<input id="twitter-token" type="hidden" name="twitter-token" value="' . $result['oauth_token'] . '" />';
				$s .= '<input id="twitter-token2" type="hidden" name="twitter-token2" value="' . $result['oauth_token_secret'] . '" />';
				$s .= '</div><div class="clear"></div>';
				$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div>';
			} catch (TwitterOAuthException $e) {
				$s .= '<p>' . DI::l10n()->t('An error occured: ') . $e->getMessage() . '</p>';
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
					<p>' . DI::l10n()->t('Currently connected to: ') . '<a href="https://twitter.com/' . $details->screen_name . '" target="_twitter">' . $details->screen_name . '</a>
						<button type="submit" name="twitter-disconnect" value="1">' . DI::l10n()->t('Disconnect') . '</button>
					</p>
					<p id="twitter-info-block">
						<a href="https://twitter.com/' . $details->screen_name . '" target="_twitter"><img id="twitter-avatar" src="' . $details->profile_image_url . '" /></a>
						<em>' . $details->description . '</em>
					</p>
				</div>';
				} else {
					$s .= '<div id="twitter-info" >
					<p>Invalid Twitter info</p>
					<button type="submit" name="twitter-disconnect" value="1">' . DI::l10n()->t('Disconnect') . '</button>
					</div>';
					Logger::notice('Invalid twitter info (verify credentials).', ['auth' => TwitterOAuth::class]);
				}
				$s .= '<div class="clear"></div>';

				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-enable', DI::l10n()->t('Allow posting to Twitter'), $enabled, DI::l10n()->t('If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.')]
				]);
				if ($user['hidewall']) {
					$s .= '<p>' . DI::l10n()->t('<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.') . '</p>';
				}
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-default', DI::l10n()->t('Send public postings to Twitter by default'), $defenabled, '']
				]);
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-mirror', DI::l10n()->t('Mirror all posts from twitter that are no replies'), $mirrorenabled, '']
				]);
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-import', DI::l10n()->t('Import the remote timeline'), $importenabled, '']
				]);
				$s .= Renderer::replaceMacros($field_checkbox, [
					'$field' => ['twitter-create_user', DI::l10n()->t('Automatically create contacts'), $create_userenabled, DI::l10n()->t('This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here.')]
				]);
				$s .= '<div class="clear"></div>';
				$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div>';
			} catch (TwitterOAuthException $e) {
				$s .= '<p>' . DI::l10n()->t('An error occured: ') . $e->getMessage() . '</p>';
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
	if (($post['extid'] == Protocol::TWITTER) || twitter_get_id($post['extid'])) {
		$b['execute'] = false;
		return;
	}

	if (substr($post['app'], 0, 7) == 'Twitter') {
		$b['execute'] = false;
		return;
	}

	if (DI::pConfig()->get($post['uid'], 'twitter', 'import')) {
		// Don't fork if it isn't a reply to a twitter post
		if (($post['parent'] != $post['id']) && !Post::exists(['id' => $post['parent'], 'network' => Protocol::TWITTER])) {
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

	$twitter_post = intval(DI::pConfig()->get(local_user(), 'twitter', 'post'));
	$twitter_enable = (($twitter_post && !empty($_REQUEST['twitter_enable'])) ? intval($_REQUEST['twitter_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(), 'twitter', 'post_by_default'))) {
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

function twitter_probe_detect(App $a, array &$hookData)
{
	// Don't overwrite an existing result
	if ($hookData['result']) {
		return;
	}

	// Avoid a lookup for the wrong network
	if (!in_array($hookData['network'], ['', Protocol::TWITTER])) {
		return;
	}

	if (preg_match('=([^@]+)@(?:mobile\.)?twitter\.com$=i', $hookData['uri'], $matches)) {
		$nick = $matches[1];
	} elseif (preg_match('=^https?://(?:mobile\.)?twitter\.com/(.+)=i', $hookData['uri'], $matches)) {
		$nick = $matches[1];
	} else {
		return;
	}

	$user = twitter_fetchuser($nick);

	if ($user) {
		$hookData['result'] = twitter_user_to_contact($user);
	}
}

function twitter_api_post(string $apiPath, string $pid, int $uid): ?bool
{
	if (empty($pid)) {
		return false;
	}

	return twitter_api_call($uid, $apiPath, ['id' => $pid]);
}

function twitter_api_call(int $uid, string $apiPath, array $parameters = []): ?bool
{
	$ckey = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');
	$otoken = DI::pConfig()->get($uid, 'twitter', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'twitter', 'oauthsecret');

	// If the addon is not configured (general or for this user) quit here
	if (empty($ckey) || empty($csecret) || empty($otoken) || empty($osecret)) {
		return null;
	}

	try {
		$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
		$result = $connection->post($apiPath, $parameters);

		if ($connection->getLastHttpCode() != 200) {
			throw new Exception($result->errors[0]->message ?? json_encode($result), $connection->getLastHttpCode());
		}

		if (!empty($result->errors)) {
			throw new Exception($result->errors[0]->message, $result->errors[0]->code);
		}

		Logger::info('[twitter] API call successful', ['apiPath' => $apiPath, 'parameters' => $parameters]);
		Logger::debug('[twitter] API call result', ['apiPath' => $apiPath, 'parameters' => $parameters, 'result' => $result]);

		return true;
	} catch (TwitterOAuthException $twitterOAuthException) {
		Logger::warning('Unable to communicate with twitter', ['apiPath' => $apiPath, 'parameters' => $parameters, 'code' => $twitterOAuthException->getCode(), 'exception' => $twitterOAuthException]);
		return false;
	} catch (Exception $e) {
		Logger::notice('[twitter] API call failed', ['apiPath' => $apiPath, 'parameters' => $parameters, 'code' => $e->getCode(), 'message' => $e->getMessage()]);
		return false;
	}
}

function twitter_get_id(string $uri)
{
	if ((substr($uri, 0, 9) != 'twitter::') || (strlen($uri) <= 9)) {
		return 0;
	}

	$id = substr($uri, 9);
	if (!is_numeric($id)) {
		return 0;
	}

	return (int)$id;
}

function twitter_post_hook(App $a, array &$b)
{
	// Post to Twitter
	if (!DI::pConfig()->get($b["uid"], 'twitter', 'import')
		&& ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], $b['body']);

	$thr_parent = null;

	if ($b['parent'] != $b['id']) {
		Logger::debug('Got comment', ['item' => $b]);

		// Looking if its a reply to a twitter post
		if (!twitter_get_id($b["parent-uri"]) &&
			!twitter_get_id($b["extid"]) &&
			!twitter_get_id($b["thr-parent"])) {
			Logger::info('No twitter post', ['parent' => $b["parent"]]);
			return;
		}

		$condition = ['uri' => $b["thr-parent"], 'uid' => $b["uid"]];
		$thr_parent = Post::selectFirst(['uri', 'extid', 'author-link', 'author-nick', 'author-network'], $condition);
		if (!DBA::isResult($thr_parent)) {
			Logger::warning('No parent found', ['thr-parent' => $b["thr-parent"]]);
			return;
		}

		if ($thr_parent['author-network'] == Protocol::TWITTER) {
			$nickname = '@[url=' . $thr_parent['author-link'] . ']' . $thr_parent['author-nick'] . '[/url]';
			$nicknameplain = '@' . $thr_parent['author-nick'];

			Logger::info('Comparing', ['nickname' => $nickname, 'nicknameplain' => $nicknameplain, 'body' => $b["body"]]);
			if ((strpos($b["body"], $nickname) === false) && (strpos($b["body"], $nicknameplain) === false)) {
				$b["body"] = $nickname . " " . $b["body"];
			}
		}

		Logger::debug('Parent found', ['parent' => $thr_parent]);
	} else {
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

	/**
	 * @TODO This can't work at the moment:
	 *  - Posts created on Friendica and mirrored to Twitter don't have a Twitter ID
	 *  - Posts created on Twitter and mirrored on Friendica do not trigger the notifier hook this is part of.
	 */
	//if (($b['verb'] == Activity::POST) && $b['deleted']) {
	//	twitter_api_post('statuses/destroy', twitter_get_id($thr_parent['uri']), $b['uid']);
	//}

	if ($b['verb'] == Activity::LIKE) {
		Logger::info('Like', ['uid' => $b['uid'], 'id' => twitter_get_id($b["thr-parent"])]);

		twitter_api_post($b['deleted'] ? 'favorites/destroy' : 'favorites/create', twitter_get_id($b["thr-parent"]), $b["uid"]);

		return;
	}

	if ($b['verb'] == Activity::ANNOUNCE) {
		Logger::info('Retweet', ['uid' => $b['uid'], 'id' => twitter_get_id($b["thr-parent"])]);
		if ($b['deleted']) {
			/**
			 * @TODO This can't work at the moment:
			 * - Twitter post reshare removal doesn't seem to trigger the notifier hook this is part of
			 */
			//twitter_api_post('statuses/destroy', twitter_get_id($thr_parent['extid']), $b['uid']);
		} else {
			twitter_retweet($b["uid"], twitter_get_id($b["thr-parent"]));
		}

		return;
	}

	if ($b['deleted'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	// if post comes from twitter don't send it back
	if (($b['extid'] == Protocol::TWITTER) || twitter_get_id($b['extid'])) {
		return;
	}

	if ($b['app'] == "Twitter") {
		return;
	}

	Logger::notice('twitter post invoked', ['id' => $b['id'], 'guid' => $b['guid']]);

	DI::pConfig()->load($b['uid'], 'twitter');

	$ckey    = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');
	$otoken  = DI::pConfig()->get($b['uid'], 'twitter', 'oauthtoken');
	$osecret = DI::pConfig()->get($b['uid'], 'twitter', 'oauthsecret');

	if ($ckey && $csecret && $otoken && $osecret) {
		Logger::info('We have customer key and oauth stuff, going to send.');

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

		$msgarr = Plaintext::getPost($b, $max_char, true, BBCode::TWITTER);
		Logger::info('Got plaintext', ['id' => $b['id'], 'message' => $msgarr]);
		$msg = $msgarr["text"];

		if (($msg == "") && isset($msgarr["title"])) {
			$msg = Plaintext::shorten($msgarr["title"], $max_char - 50, $b['uid']);
		}

		// Add the link to the body if the type isn't a photo or there are more than 4 images in the post
		if (!empty($msgarr['url']) && (strpos($msg, $msgarr['url']) === false) && (($msgarr['type'] != 'photo') || empty($msgarr['images']) || (count($msgarr['images']) > 4))) {
			$msg .= "\n" . $msgarr['url'];
		}

		if (empty($msg)) {
			Logger::notice('Empty message', ['id' => $b['id']]);
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

					$img_str = DI::httpClient()->fetch($image['url']);

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
							Logger::info('Metadata create', ['id' => $b['id'], 'data' => $data, 'return' => $ret]);
						}
					} else {
						Logger::error('Failed upload', ['id' => $b['id'], 'image' => $image['url'], 'return' => $media]);
						throw new Exception('Failed upload of ' . $image['url']);
					}
				}
				$post['media_ids'] = implode(',', $media_ids);
				if (empty($post['media_ids'])) {
					unset($post['media_ids']);
				}
			} catch (Exception $e) {
				Logger::warning('Exception when trying to send to Twitter', ['id' => $b['id'], 'message' => $e->getMessage()]);
			}
		}

		$post['status'] = $msg;

		if ($thr_parent) {
			$post['in_reply_to_status_id'] = twitter_get_id($thr_parent['uri']);
		}

		$result = $connection->post('statuses/update', $post);
		Logger::info('twitter_post send', ['id' => $b['id'], 'result' => $result]);

		if (!empty($result->source)) {
			DI::config()->set("twitter", "application_name", strip_tags($result->source));
		}

		if (!empty($result->errors)) {
			Logger::error('Send to Twitter failed', ['id' => $b['id'], 'error' => $result->errors]);
			Worker::defer();
		} elseif ($thr_parent) {
			Logger::notice('Post send, updating extid', ['id' => $b['id'], 'extid' => $result->id_str]);
			Item::update(['extid' => "twitter::" . $result->id_str], ['id' => $b['id']]);
		}
	}
}

function twitter_addon_admin_post(App $a)
{
	$consumerkey    = !empty($_POST['consumerkey'])    ? Strings::escapeTags(trim($_POST['consumerkey']))    : '';
	$consumersecret = !empty($_POST['consumersecret']) ? Strings::escapeTags(trim($_POST['consumersecret'])) : '';
	DI::config()->set('twitter', 'consumerkey', $consumerkey);
	DI::config()->set('twitter', 'consumersecret', $consumersecret);
}

function twitter_addon_admin(App $a, &$o)
{
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/twitter/");

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		// name, label, value, help, [extra values]
		'$consumerkey' => ['consumerkey', DI::l10n()->t('Consumer key'), DI::config()->get('twitter', 'consumerkey'), ''],
		'$consumersecret' => ['consumersecret', DI::l10n()->t('Consumer secret'), DI::config()->get('twitter', 'consumersecret'), ''],
	]);
}

function twitter_cron(App $a)
{
	$last = DI::config()->get('twitter', 'last_poll');

	$poll_interval = intval(DI::config()->get('twitter', 'poll_interval'));
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

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'twitter', 'k' => 'mirror_posts', 'v' => true]);
	foreach ($pconfigs as $rr) {
		Logger::notice('Fetching', ['user' => $rr['uid']]);
		Worker::add(['priority' => PRIORITY_MEDIUM, 'force_priority' => true], "addon/twitter/twitter_sync.php", 1, (int) $rr['uid']);
	}

	$abandon_days = intval(DI::config()->get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'twitter', 'k' => 'import', 'v' => true]);
	foreach ($pconfigs as $rr) {
		if ($abandon_days != 0) {
			if (!DBA::exists('user', ["`uid` = ? AND `login_date` >= ?", $rr['uid'], $abandon_limit])) {
				Logger::notice('abandoned account: timeline from user will not be imported', ['user' => $rr['uid']]);
				continue;
			}
		}

		Logger::notice('importing timeline', ['user' => $rr['uid']]);
		Worker::add(['priority' => PRIORITY_MEDIUM, 'force_priority' => true], "addon/twitter/twitter_sync.php", 2, (int) $rr['uid']);
		/*
			// To-Do
			// check for new contacts once a day
			$last_contact_check = DI::pConfig()->get($rr['uid'],'pumpio','contact_check');
			if($last_contact_check)
			$next_contact_check = $last_contact_check + 86400;
			else
			$next_contact_check = 0;

			if($next_contact_check <= time()) {
			pumpio_getallusers($a, $rr["uid"]);
			DI::pConfig()->set($rr['uid'],'pumpio','contact_check',time());
			}
			*/
	}

	Logger::notice('twitter: cron_end');

	DI::config()->set('twitter', 'last_poll', time());
}

function twitter_expire(App $a)
{
	$days = DI::config()->get('twitter', 'expire');

	if ($days == 0) {
		return;
	}

	Logger::notice('Start deleting expired posts');

	$r = Post::select(['id', 'guid'], ['deleted' => true, 'network' => Protocol::TWITTER]);
	while ($row = Post::fetch($r)) {
		Logger::info('[twitter] Delete expired item', ['id' => $row['id'], 'guid' => $row['guid'], 'callstack' => \Friendica\Core\System::callstack()]);
		Item::markForDeletionById($row['id']);
	}
	DBA::close($r);

	Logger::notice('End deleting expired posts');

	Logger::notice('Start expiry');

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'twitter', 'k' => 'import', 'v' => true]);
	foreach ($pconfigs as $rr) {
		Logger::notice('twitter_expire', ['user' => $rr['uid']]);
		Item::expire($rr['uid'], $days, Protocol::TWITTER, true);
	}

	Logger::notice('End expiry');
}

function twitter_prepare_body(App $a, array &$b)
{
	if ($b["item"]["network"] != Protocol::TWITTER) {
		return;
	}

	if ($b["preview"]) {
		$max_char = 280;
		$item = $b["item"];
		$item["plink"] = DI::baseUrl()->get() . "/display/" . $item["guid"];

		$condition = ['uri' => $item["thr-parent"], 'uid' => local_user()];
		$orig_post = Post::selectFirst(['author-link'], $condition);
		if (DBA::isResult($orig_post)) {
			$nicknameplain = preg_replace("=https?://twitter.com/(.*)=ism", "$1", $orig_post["author-link"]);
			$nickname = "@[url=" . $orig_post["author-link"] . "]" . $nicknameplain . "[/url]";
			$nicknameplain = "@" . $nicknameplain;

			if ((strpos($item["body"], $nickname) === false) && (strpos($item["body"], $nicknameplain) === false)) {
				$item["body"] = $nickname . " " . $item["body"];
			}
		}

		$msgarr = Plaintext::getPost($item, $max_char, true, BBCode::TWITTER);
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

function twitter_statuses_show(string $id, TwitterOAuth $twitterOAuth = null)
{
	if ($twitterOAuth === null) {
		$ckey = DI::config()->get('twitter', 'consumerkey');
		$csecret = DI::config()->get('twitter', 'consumersecret');

		if (empty($ckey) || empty($csecret)) {
			return new stdClass();
		}

		$twitterOAuth = new TwitterOAuth($ckey, $csecret);
	}

	$parameters = ['trim_user' => false, 'tweet_mode' => 'extended', 'id' => $id, 'include_ext_alt_text' => true];

	return $twitterOAuth->get('statuses/show', $parameters);
}

/**
 * Parse Twitter status URLs since Twitter removed OEmbed
 *
 * @param App   $a
 * @param array $b Expected format:
 *                 [
 *                      'url' => [URL to parse],
 *                      'format' => 'json'|'',
 *                      'text' => Output parameter
 *                 ]
 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
 */
function twitter_parse_link(App $a, array &$b)
{
	// Only handle Twitter status URLs
	if (!preg_match('#^https?://(?:mobile\.|www\.)?twitter.com/[^/]+/status/(\d+).*#', $b['url'], $matches)) {
		return;
	}

	$status = twitter_statuses_show($matches[1]);

	if (empty($status->id)) {
		return;
	}

	$item = twitter_createpost($a, 0, $status, [], true, false, true);

	if ($b['format'] == 'json') {
		$images = [];
		foreach ($status->extended_entities->media ?? [] as $media) {
			if (!empty($media->media_url_https)) {
				$images[] = [
					'src'    => $media->media_url_https,
					'width'  => $media->sizes->thumb->w,
					'height' => $media->sizes->thumb->h,
				];
			}
		}

		$b['text'] = [
			'data' => [
				'type' => 'link',
				'url' => $item['plink'],
				'title' => DI::l10n()->t('%s on Twitter', $status->user->name),
				'text' => BBCode::toPlaintext($item['body'], false),
				'images' => $images,
			],
			'contentType' => 'attachment',
			'success' => true,
		];
	} else {
		$b['text'] = BBCode::getShareOpeningTag(
			$item['author-name'],
			$item['author-link'],
			$item['author-avatar'],
			$item['plink'],
			$item['created']
		);
		$b['text'] .= $item['body'] . '[/share]';
	}
}


/*********************
 *
 * General functions
 *
 *********************/


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
	$datarray['uid'] = $uid;
	$datarray['extid'] = 'twitter::' . $post->id;
	$datarray['title'] = '';

	if (!empty($post->retweeted_status)) {
		// We don't support nested shares, so we mustn't show quotes as shares on retweets
		$item = twitter_createpost($a, $uid, $post->retweeted_status, ['id' => 0], false, false, true, -1);

		if (empty($item['body'])) {
			return [];
		}

		$datarray['body'] = "\n" . BBCode::getShareOpeningTag(
			$item['author-name'],
			$item['author-link'],
			$item['author-avatar'],
			$item['plink'],
			$item['created']
		);

		$datarray['body'] .= $item['body'] . '[/share]';
	} else {
		$item = twitter_createpost($a, $uid, $post, ['id' => 0], false, false, false, -1);

		if (empty($item['body'])) {
			return [];
		}

		$datarray['body'] = $item['body'];
	}

	$datarray['app'] = $item['app'];
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
	$ckey    = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');
	$otoken  = DI::pConfig()->get($uid, 'twitter', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'twitter', 'oauthsecret');
	$lastid  = DI::pConfig()->get($uid, 'twitter', 'lastid');

	$application_name = DI::config()->get('twitter', 'application_name');

	if ($application_name == "") {
		$application_name = DI::baseUrl()->getHostname();
	}

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

	// Ensure to have the own contact
	try {
		twitter_fetch_own_contact($a, $uid);
	} catch (TwitterOAuthException $e) {
		Logger::warning('Error fetching own contact', ['uid' => $uid, 'message' => $e->getMessage()]);
		return;
	}

	$parameters = ["exclude_replies" => true, "trim_user" => false, "contributor_details" => true, "include_rts" => true, "tweet_mode" => "extended", "include_ext_alt_text" => true];

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	try {
		$items = $connection->get('statuses/user_timeline', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::warning('Error fetching timeline', ['uid' => $uid, 'message' => $e->getMessage()]);
		return;
	}

	if (!is_array($items)) {
		Logger::notice('No items', ['user' => $uid]);
		return;
	}

	$posts = array_reverse($items);

	Logger::notice('Start processing posts', ['from' => $lastid, 'user' => $uid, 'count' => count($posts)]);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid) {
				$lastid = $post->id_str;
				DI::pConfig()->set($uid, 'twitter', 'lastid', $lastid);
			}

			if ($first_time) {
				continue;
			}

			if (!stristr($post->source, $application_name)) {
				Logger::info('Preparing mirror post', ['twitter-id' => $post->id_str, 'uid' => $uid]);

				$mirrorpost = twitter_do_mirrorpost($a, $uid, $post);

				if (empty($mirrorpost['body'])) {
					continue;
				}

				Logger::info('Posting mirror post', ['twitter-id' => $post->id_str, 'uid' => $uid]);

				Post\Delayed::add($mirrorpost['extid'], $mirrorpost, PRIORITY_MEDIUM, Post\Delayed::UNPREPARED);
			}
		}
	}
	DI::pConfig()->set($uid, 'twitter', 'lastid', $lastid);
	Logger::info('Last ID for user ' . $uid . ' is now ' . $lastid);
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

function twitter_get_relation($uid, $target, $contact = [])
{
	if (isset($contact['rel'])) {
		$relation = $contact['rel'];
	} else {
		$relation = 0;
	}

	$ckey = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');
	$otoken = DI::pConfig()->get($uid, 'twitter', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'twitter', 'oauthsecret');
	$own_id = DI::pConfig()->get($uid, 'twitter', 'own_id');

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);
	$parameters = ['source_id' => $own_id, 'target_screen_name' => $target];

	try {
		$status = $connection->get('friendships/show', $parameters);
		if ($connection->getLastHttpCode() !== 200) {
			throw new Exception($status->errors[0]->message ?? 'HTTP response code ' . $connection->getLastHttpCode(), $status->errors[0]->code ?? $connection->getLastHttpCode());
		}

		$following = $status->relationship->source->following;
		$followed = $status->relationship->source->followed_by;

		if ($following && !$followed) {
			$relation = Contact::SHARING;
		} elseif (!$following && $followed) {
			$relation = Contact::FOLLOWER;
		} elseif ($following && $followed) {
			$relation = Contact::FRIEND;
		} elseif (!$following && !$followed) {
			$relation = 0;
		}

		Logger::info('Fetched friendship relation', ['user' => $uid, 'target' => $target, 'relation' => $relation]);
	} catch (Throwable $e) {
		Logger::warning('Error fetching friendship status', ['uid' => $uid, 'target' => $target, 'message' => $e->getMessage()]);
	}

	return $relation;
}

/**
 * @param $data
 * @return array
 */
function twitter_user_to_contact($data)
{
	if (empty($data->id_str)) {
		return [];
	}

	$baseurl = 'https://twitter.com';
	$url = $baseurl . '/' . $data->screen_name;
	$addr = $data->screen_name . '@twitter.com';

	$fields = [
		'url'      => $url,
		'network'  => Protocol::TWITTER,
		'alias'    => 'twitter::' . $data->id_str,
		'baseurl'  => $baseurl,
		'name'     => $data->name,
		'nick'     => $data->screen_name,
		'addr'     => $addr,
		'location' => $data->location,
		'about'    => $data->description,
		'photo'    => twitter_fix_avatar($data->profile_image_url_https),
		'header'   => $data->profile_banner_url ?? $data->profile_background_image_url_https,
	];

	return $fields;
}

function twitter_fetch_contact($uid, $data, $create_user)
{
	$fields = twitter_user_to_contact($data);

	if (empty($fields)) {
		return -1;
	}

	// photo comes from twitter_user_to_contact but shouldn't be saved directly in the contact row
	$avatar = $fields['photo'];
	unset($fields['photo']);

	// Update the public contact
	$pcontact = DBA::selectFirst('contact', ['id'], ['uid' => 0, 'alias' => "twitter::" . $data->id_str]);
	if (DBA::isResult($pcontact)) {
		$cid = $pcontact['id'];
	} else {
		$cid = Contact::getIdForURL($fields['url'], 0, false, $fields);
	}

	if (!empty($cid)) {
		Contact::update($fields, ['id' => $cid]);
		Contact::updateAvatar($cid, $avatar);
	} else {
		Logger::warning('No contact found', ['fields' => $fields]);
	}

	$contact = DBA::selectFirst('contact', [], ['uid' => $uid, 'alias' => "twitter::" . $data->id_str]);
	if (!DBA::isResult($contact) && empty($cid)) {
		Logger::warning('User contact not found', ['uid' => $uid, 'twitter-id' => $data->id_str]);
		return 0;
	} elseif (!$create_user) {
		return $cid;
	}

	if (!DBA::isResult($contact)) {
		$relation = twitter_get_relation($uid, $data->screen_name);

		// create contact record
		$fields['uid'] = $uid;
		$fields['created'] = DateTimeFormat::utcNow();
		$fields['nurl'] = Strings::normaliseLink($fields['url']);
		$fields['poll'] = 'twitter::' . $data->id_str;
		$fields['rel'] = $relation;
		$fields['priority'] = 1;
		$fields['writable'] = true;
		$fields['blocked'] = false;
		$fields['readonly'] = false;
		$fields['pending'] = false;

		if (!Contact::insert($fields)) {
			return false;
		}

		$contact_id = DBA::lastInsertId();

		Group::addMember(User::getDefaultGroup($uid), $contact_id);

		Contact::updateAvatar($contact_id, $avatar);
	} else {
		if ($contact["readonly"] || $contact["blocked"]) {
			Logger::notice('Contact is blocked or readonly.', ['nickname' => $contact["nick"]]);
			return -1;
		}

		$contact_id = $contact['id'];
		$update = false;

		// Update the contact relation once per day
		if ($contact['updated'] < DateTimeFormat::utc('now -24 hours')) {
			$fields['rel'] = twitter_get_relation($uid, $data->screen_name, $contact);
			$update = true;
		}

		Contact::updateAvatar($contact['id'], $avatar);

		if ($contact['name'] != $data->name) {
			$fields['name-date'] = $fields['uri-date'] = DateTimeFormat::utcNow();
			$update = true;
		}

		if ($contact['nick'] != $data->screen_name) {
			$fields['uri-date'] = DateTimeFormat::utcNow();
			$update = true;
		}

		if (($contact['location'] != $data->location) || ($contact['about'] != $data->description)) {
			$update = true;
		}

		if ($update) {
			$fields['updated'] = DateTimeFormat::utcNow();
			Contact::update($fields, ['id' => $contact['id']]);
			Logger::info('Updated contact', ['id' => $contact['id'], 'nick' => $data->screen_name]);
		}
	}

	return $contact_id;
}

/**
 * @param string $screen_name
 * @return stdClass|null
 * @throws Exception
 */
function twitter_fetchuser($screen_name)
{
	$ckey = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');

	try {
		// Fetching user data
		$connection = new TwitterOAuth($ckey, $csecret);
		$parameters = ['screen_name' => $screen_name];
		$user = $connection->get('users/show', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::warning('Error fetching user', ['user' => $screen_name, 'message' => $e->getMessage()]);
		return null;
	}

	if (!is_object($user)) {
		return null;
	}

	return $user;
}

/**
 * Replaces Twitter entities with Friendica-friendly links.
 *
 * The Twitter API gives indices for each entity, which allows for fine-grained replacement.
 *
 * First, we need to collect everything that needs to be replaced, what we will replace it with, and the start index.
 * Then we sort the indices decreasingly, and we replace from the end of the body to the start in order for the next
 * index to be correct even after the last replacement.
 *
 * @param string   $body
 * @param stdClass $status
 * @return array
 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
 */
function twitter_expand_entities($body, stdClass $status)
{
	$plain = $body;
	$contains_urls = false;

	$taglist = [];

	$replacementList = [];

	foreach ($status->entities->hashtags AS $hashtag) {
		$replace = '#[url=' . DI::baseUrl()->get() . '/search?tag=' . $hashtag->text . ']' . $hashtag->text . '[/url]';
		$taglist['#' . $hashtag->text] = ['#', $hashtag->text, ''];

		$replacementList[$hashtag->indices[0]] = [
			'replace' => $replace,
			'length' => $hashtag->indices[1] - $hashtag->indices[0],
		];
	}

	foreach ($status->entities->user_mentions AS $mention) {
		$replace = '@[url=https://twitter.com/' . rawurlencode($mention->screen_name) . ']' . $mention->screen_name . '[/url]';
		$taglist['@' . $mention->screen_name] = ['@', $mention->screen_name, 'https://twitter.com/' . rawurlencode($mention->screen_name)];

		$replacementList[$mention->indices[0]] = [
			'replace' => $replace,
			'length' => $mention->indices[1] - $mention->indices[0],
		];
	}

	foreach ($status->entities->urls ?? [] as $url) {
		$plain = str_replace($url->url, '', $plain);

		if ($url->url && $url->expanded_url && $url->display_url) {
			// Quote tweet, we just remove the quoted tweet URL from the body, the share block will be added later.
			if (!empty($status->quoted_status) && isset($status->quoted_status_id_str)
				&& substr($url->expanded_url, -strlen($status->quoted_status_id_str)) == $status->quoted_status_id_str
			) {
				$replacementList[$url->indices[0]] = [
					'replace' => '',
					'length' => $url->indices[1] - $url->indices[0],
				];
				continue;
			}

			$contains_urls = true;

			$expanded_url = $url->expanded_url;

			// Quickfix: Workaround for URL with '[' and ']' in it
			if (strpos($expanded_url, '[') || strpos($expanded_url, ']')) {
				$expanded_url = $url->url;
			}

			$replacementList[$url->indices[0]] = [
				'replace' => '[url=' . $expanded_url . ']' . $url->display_url . '[/url]',
				'length' => $url->indices[1] - $url->indices[0],
			];
		}
	}

	krsort($replacementList);

	foreach ($replacementList as $startIndex => $parameters) {
		$body = Strings::substringReplace($body, $parameters['replace'], $startIndex, $parameters['length']);
	}

	$body = trim($body);

	return ['body' => trim($body), 'plain' => trim($plain), 'taglist' => $taglist, 'urls' => $contains_urls];
}

/**
 * Store entity attachments
 *
 * @param integer $uriid
 * @param object $post Twitter object with the post
 */
function twitter_store_attachments(int $uriid, $post)
{
	if (!empty($post->extended_entities->media)) {
		foreach ($post->extended_entities->media AS $medium) {
			switch ($medium->type) {
				case 'photo':
					$attachment = ['uri-id' => $uriid, 'type' => Post\Media::IMAGE];

					$attachment['url'] = $medium->media_url_https . '?name=large';
					$attachment['width'] = $medium->sizes->large->w;
					$attachment['height'] = $medium->sizes->large->h;

					if ($medium->sizes->small->w != $attachment['width']) {
						$attachment['preview'] = $medium->media_url_https . '?name=small';
						$attachment['preview-width'] = $medium->sizes->small->w;
						$attachment['preview-height'] = $medium->sizes->small->h;
					}

					$attachment['name'] = $medium->display_url ?? null;
					$attachment['description'] = $medium->ext_alt_text ?? null;
					Logger::debug('Photo attachment', ['attachment' => $attachment]);
					Post\Media::insert($attachment);
					break;
				case 'video':
				case 'animated_gif':
					$attachment = ['uri-id' => $uriid, 'type' => Post\Media::VIDEO];
					if (is_array($medium->video_info->variants)) {
						$bitrate = 0;
						// We take the video with the highest bitrate
						foreach ($medium->video_info->variants AS $variant) {
							if (($variant->content_type == 'video/mp4') && ($variant->bitrate >= $bitrate)) {
								$attachment['url'] = $variant->url;
								$bitrate = $variant->bitrate;
							}
						}
					}

					$attachment['name'] = $medium->display_url ?? null;
					$attachment['preview'] = $medium->media_url_https . ':small';
					$attachment['preview-width'] = $medium->sizes->small->w;
					$attachment['preview-height'] = $medium->sizes->small->h;
					$attachment['description'] = $medium->ext_alt_text ?? null;
					Logger::debug('Video attachment', ['attachment' => $attachment]);
					Post\Media::insert($attachment);
					break;
				default:
					Logger::notice('Unknown media type', ['medium' => $medium]);
			}
		}
	}

	if (!empty($post->entities->urls)) {
		foreach ($post->entities->urls as $url) {
			$attachment = ['uri-id' => $uriid, 'type' => Post\Media::UNKNOWN, 'url' => $url->expanded_url, 'name' => $url->display_url];
			Logger::debug('Attached link', ['attachment' => $attachment]);
			Post\Media::insert($attachment);
		}
	}
}

/**
 * @brief Fetch media entities and add media links to the body
 *
 * @param object  $post      Twitter object with the post
 * @param array   $postarray Array of the item that is about to be posted
 * @param integer $uriid URI Id used to store tags. -1 = don't store tags for this post.
 */
function twitter_media_entities($post, array &$postarray, int $uriid = -1)
{
	// There are no media entities? So we quit.
	if (empty($post->extended_entities->media)) {
		return;
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
				$postarray['post-type'] = Item::PT_IMAGE;
				break;
			case 'video':
				// Currently deactivated, since this causes the video to be display before the content
				// We have to figure out a better way for declaring the post type and the display style.
				//$postarray['post-type'] = Item::PT_VIDEO;
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
		}
	}

	if ($uriid != -1) {
		foreach ($media AS $key => $value) {
			$postarray['body'] = str_replace($key, '', $postarray['body']);
		}
		return;
	}

	// Now we replace the media urls.
	foreach ($media AS $key => $value) {
		$postarray['body'] = str_replace($key, "\n" . $value . "\n", $postarray['body']);
	}
}

/**
 * Undocumented function
 *
 * @param App $a
 * @param integer $uid User ID
 * @param object $post Incoming Twitter post
 * @param array $self
 * @param bool $create_user Should users be created?
 * @param bool $only_existing_contact Only import existing contacts if set to "true"
 * @param bool $noquote
 * @param integer $uriid URI Id used to store tags. 0 = create a new one; -1 = don't store tags for this post.
 * @return array item array
 */
function twitter_createpost(App $a, $uid, $post, array $self, $create_user, $only_existing_contact, $noquote, int $uriid = 0)
{
	$postarray = [];
	$postarray['network'] = Protocol::TWITTER;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['uri'] = "twitter::" . $post->id_str;
	$postarray['protocol'] = Conversation::PARCEL_TWITTER;
	$postarray['source'] = json_encode($post);
	$postarray['direction'] = Conversation::PULL;

	if (empty($uriid)) {
		$uriid = $postarray['uri-id'] = ItemURI::insert(['uri' => $postarray['uri']]);
	}

	// Don't import our own comments
	if (Post::exists(['extid' => $postarray['uri'], 'uid' => $uid])) {
		Logger::info('Item found', ['extid' => $postarray['uri']]);
		return [];
	}

	$contactid = 0;

	if ($post->in_reply_to_status_id_str != "") {
		$thr_parent = "twitter::" . $post->in_reply_to_status_id_str;

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
		$own_id = DI::pConfig()->get($uid, 'twitter', 'own_id');

		if ($post->user->id_str == $own_id) {
			$self = Contact::selectFirst(['id', 'name', 'url', 'photo'], ['self' => true, 'uid' => $uid]);
			if (DBA::isResult($self)) {
				$contactid = $self['id'];

				$postarray['owner-name']   = $self['name'];
				$postarray['owner-link']   = $self['url'];
				$postarray['owner-avatar'] = $self['photo'];
			} else {
				Logger::error('No self contact found', ['uid' => $uid]);
				return [];
			}
		}
		// Don't create accounts of people who just comment something
		$create_user = false;
	} else {
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
		Logger::info('Contact ID is zero or less than zero.');
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
		$postarray['private'] = Item::PRIVATE;
		$postarray['allow_cid'] = '<' . $self['id'] . '>';
	} else {
		$postarray['private'] = Item::UNLISTED;
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
	twitter_media_entities($post, $postarray, $uriid);

	$converted = twitter_expand_entities($postarray['body'], $post);

	// When the post contains external links then images or videos are just "decorations".
	if (!empty($converted['urls'])) {
		$postarray['post-type'] = Item::PT_NOTE;
	}

	$postarray['body'] = $converted['body'];
	$postarray['created'] = DateTimeFormat::utc($post->created_at);
	$postarray['edited'] = DateTimeFormat::utc($post->created_at);

	if ($uriid > 0) {
		twitter_store_tags($uriid, $converted['taglist']);
		twitter_store_attachments($uriid, $post);
	}

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
		} else {
			$retweet['source'] = $postarray['source'];
			$retweet['direction'] = $postarray['direction'];
			$retweet['private'] = $postarray['private'];
			$retweet['allow_cid'] = $postarray['allow_cid'];
			$retweet['contact-id'] = $postarray['contact-id'];
			$retweet['owner-name'] = $postarray['owner-name'];
			$retweet['owner-link'] = $postarray['owner-link'];
			$retweet['owner-avatar'] = $postarray['owner-avatar'];

			$postarray = $retweet;
		}
	}

	if (!empty($post->quoted_status)) {
		if ($noquote) {
			// To avoid recursive share blocks we just provide the link to avoid removing quote context.
			$postarray['body'] .= "\n\nhttps://twitter.com/" . $post->quoted_status->user->screen_name . "/status/" . $post->quoted_status->id_str;
		} else {
			$quoted = twitter_createpost($a, 0, $post->quoted_status, $self, false, false, true);
			if (!empty($quoted['body'])) {
				Item::insert($quoted);
				$post = Post::selectFirst(['guid', 'uri-id'], ['uri' => $quoted['uri'], 'uid' => 0]);
				Logger::info('Stored quoted post', ['uid' => $uid, 'uri-id' => $uriid, 'post' => $post]);

				$postarray['body'] .= "\n" . BBCode::getShareOpeningTag(
						$quoted['author-name'],
						$quoted['author-link'],
						$quoted['author-avatar'],
						$quoted['plink'],
						$quoted['created'],
						$post['guid'] ?? ''
					);

				$postarray['body'] .= $quoted['body'] . '[/share]';
			} else {
				// Quoted post author is blocked/ignored, so we just provide the link to avoid removing quote context.
				$postarray['body'] .= "\n\nhttps://twitter.com/" . $post->quoted_status->user->screen_name . "/status/" . $post->quoted_status->id_str;
			}
		}
	}

	return $postarray;
}

/**
 * Store tags and mentions
 *
 * @param integer $uriid
 * @param array $taglist
 */
function twitter_store_tags(int $uriid, array $taglist)
{
	foreach ($taglist as $tag) {
		Tag::storeByHash($uriid, $tag[0], $tag[1], $tag[2]);
	}
}

function twitter_fetchparentposts(App $a, $uid, $post, TwitterOAuth $connection, array $self)
{
	Logger::info('Fetching parent posts', ['user' => $uid, 'post' => $post->id_str]);

	$posts = [];

	while (!empty($post->in_reply_to_status_id_str)) {
		try {
			$post = twitter_statuses_show($post->in_reply_to_status_id_str, $connection);
		} catch (TwitterOAuthException $e) {
			Logger::warning('Error fetching parent post', ['uid' => $uid, 'post' => $post->id_str, 'message' => $e->getMessage()]);
			break;
		}

		if (empty($post)) {
			Logger::info("twitter_fetchparentposts: Can't fetch post");
			break;
		}

		if (empty($post->id_str)) {
			Logger::info("twitter_fetchparentposts: This is not a post", ['post' => $post]);
			break;
		}

		if (Post::exists(['uri' => 'twitter::' . $post->id_str, 'uid' => $uid])) {
			break;
		}

		$posts[] = $post;
	}

	Logger::info("twitter_fetchparentposts: Fetching " . count($posts) . " parents");

	$posts = array_reverse($posts);

	if (!empty($posts)) {
		foreach ($posts as $post) {
			$postarray = twitter_createpost($a, $uid, $post, $self, false, !DI::pConfig()->get($uid, 'twitter', 'create_user'), false);

			if (empty($postarray['body'])) {
				continue;
			}

			$item = Item::insert($postarray);

			$postarray["id"] = $item;

			Logger::notice('twitter_fetchparentpost: User ' . $self["nick"] . ' posted parent timeline item ' . $item);
		}
	}
}

function twitter_fetchhometimeline(App $a, $uid)
{
	$ckey    = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');
	$otoken  = DI::pConfig()->get($uid, 'twitter', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'twitter', 'oauthsecret');
	$create_user = DI::pConfig()->get($uid, 'twitter', 'create_user');
	$mirror_posts = DI::pConfig()->get($uid, 'twitter', 'mirror_posts');

	Logger::info('Fetching timeline', ['uid' => $uid]);

	$application_name = DI::config()->get('twitter', 'application_name');

	if ($application_name == "") {
		$application_name = DI::baseUrl()->getHostname();
	}

	$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

	try {
		$own_contact = twitter_fetch_own_contact($a, $uid);
	} catch (TwitterOAuthException $e) {
		Logger::warning('Error fetching own contact', ['uid' => $uid, 'message' => $e->getMessage()]);
		return;
	}

	$contact = Contact::selectFirst(['nick'], ['id' => $own_contact, 'uid' => $uid]);
	if (DBA::isResult($contact)) {
		$own_id = $contact['nick'];
	} else {
		Logger::warning('Own twitter contact not found', ['uid' => $uid]);
		return;
	}

	$self = User::getOwnerDataById($uid);
	if ($self === false) {
		Logger::warning('Own contact not found', ['uid' => $uid]);
		return;
	}

	$parameters = ["exclude_replies" => false, "trim_user" => false, "contributor_details" => true, "include_rts" => true, "tweet_mode" => "extended", "include_ext_alt_text" => true];
	//$parameters["count"] = 200;
	// Fetching timeline
	$lastid = DI::pConfig()->get($uid, 'twitter', 'lasthometimelineid');

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	try {
		$items = $connection->get('statuses/home_timeline', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::warning('Error fetching home timeline', ['uid' => $uid, 'message' => $e->getMessage()]);
		return;
	}

	if (!is_array($items)) {
		Logger::warning('home timeline is no array', ['items' => $items]);
		return;
	}

	if (empty($items)) {
		Logger::notice('No new timeline content', ['uid' => $uid]);
		return;
	}

	$posts = array_reverse($items);

	Logger::notice('Processing timeline', ['lastid' => $lastid, 'uid' => $uid, 'count' => count($posts)]);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid) {
				$lastid = $post->id_str;
				DI::pConfig()->set($uid, 'twitter', 'lasthometimelineid', $lastid);
			}

			if ($first_time) {
				continue;
			}

			if (stristr($post->source, $application_name) && $post->user->screen_name == $own_id) {
				Logger::info("Skip previously sent post");
				continue;
			}

			if ($mirror_posts && $post->user->screen_name == $own_id && $post->in_reply_to_status_id_str == "") {
				Logger::info("Skip post that will be mirrored");
				continue;
			}

			if ($post->in_reply_to_status_id_str != "") {
				twitter_fetchparentposts($a, $uid, $post, $connection, $self);
			}

			Logger::info('Preparing post ' . $post->id_str . ' for user ' . $uid);

			$postarray = twitter_createpost($a, $uid, $post, $self, $create_user, true, false);

			if (empty($postarray['body']) || trim($postarray['body']) == "") {
				Logger::info('Empty body for post ' . $post->id_str . ' and user ' . $uid);
				continue;
			}

			$notify = false;

			if (empty($postarray['thr-parent'])) {
				$contact = DBA::selectFirst('contact', [], ['id' => $postarray['contact-id'], 'self' => false]);
				if (DBA::isResult($contact) && Item::isRemoteSelf($contact, $postarray)) {
					$notify = PRIORITY_MEDIUM;
				}
			}

			$item = Item::insert($postarray, $notify);
			$postarray["id"] = $item;

			Logger::notice('User ' . $uid . ' posted home timeline item ' . $item);
		}
	}
	DI::pConfig()->set($uid, 'twitter', 'lasthometimelineid', $lastid);

	Logger::info('Last timeline ID for user ' . $uid . ' is now ' . $lastid);

	// Fetching mentions
	$lastid = DI::pConfig()->get($uid, 'twitter', 'lastmentionid');

	$first_time = ($lastid == "");

	if ($lastid != "") {
		$parameters["since_id"] = $lastid;
	}

	try {
		$items = $connection->get('statuses/mentions_timeline', $parameters);
	} catch (TwitterOAuthException $e) {
		Logger::warning('Error fetching mentions', ['uid' => $uid, 'message' => $e->getMessage()]);
		return;
	}

	if (!is_array($items)) {
		Logger::warning("mentions are no arrays", ['items' => $items]);
		return;
	}

	$posts = array_reverse($items);

	Logger::info("Fetching mentions for user " . $uid . " " . sizeof($posts) . " items");

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

			Logger::notice('User ' . $uid . ' posted mention timeline item ' . $item);
		}
	}

	DI::pConfig()->set($uid, 'twitter', 'lastmentionid', $lastid);

	Logger::info('Last mentions ID for user ' . $uid . ' is now ' . $lastid);
}

function twitter_fetch_own_contact(App $a, $uid)
{
	$ckey    = DI::config()->get('twitter', 'consumerkey');
	$csecret = DI::config()->get('twitter', 'consumersecret');
	$otoken  = DI::pConfig()->get($uid, 'twitter', 'oauthtoken');
	$osecret = DI::pConfig()->get($uid, 'twitter', 'oauthsecret');

	$own_id = DI::pConfig()->get($uid, 'twitter', 'own_id');

	$contact_id = 0;

	if ($own_id == "") {
		$connection = new TwitterOAuth($ckey, $csecret, $otoken, $osecret);

		// Fetching user data
		// get() may throw TwitterOAuthException, but we will catch it later
		$user = $connection->get('account/verify_credentials');
		if (empty($user->id_str)) {
			return false;
		}

		DI::pConfig()->set($uid, 'twitter', 'own_id', $user->id_str);

		$contact_id = twitter_fetch_contact($uid, $user, true);
	} else {
		$contact = Contact::selectFirst(['id'], ['uid' => $uid, 'alias' => 'twitter::' . $own_id]);
		if (DBA::isResult($contact)) {
			$contact_id = $contact['id'];
		} else {
			DI::pConfig()->delete($uid, 'twitter', 'own_id');
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
	return twitter_retweet($uid, $id);
}

function twitter_retweet(int $uid, int $id, int $item_id = 0)
{
	Logger::info('Retweeting', ['user' => $uid, 'id' => $id]);

	$result = twitter_api_post('statuses/retweet', $id, $uid);

	Logger::info('Retweeted', ['user' => $uid, 'id' => $id, 'result' => $result]);

	if (!empty($item_id) && !empty($result->id_str)) {
		Logger::notice('Update extid', ['id' => $item_id, 'extid' => $result->id_str]);
		Item::update(['extid' => "twitter::" . $result->id_str], ['id' => $item_id]);
	}

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
	if (empty($author_contact)) {
		return $content . "\n\n" . $attributes['link'];
	}

	if (!empty($author_contact['network']) && ($author_contact['network'] == Protocol::TWITTER)) {
		$mention = '@' . $author_contact['nick'];
	} else {
		$mention = $author_contact['addr'];
	}

	return ($is_quote_share ? "\n\n" : '' ) . 'RT ' . $mention . ': ' . $content . "\n\n" . $attributes['link'];
}
