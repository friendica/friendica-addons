<?php

/**
 * Name: AT Protocol Atmosphere Connector (Bluesky, Eurosky, Blacksky, ...)
 * Description: Post to the Atmosphere via the AT Protocol, import timelines and feeds
 * Version: 1.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 * @todo
 * Currently technical issues in the core:
 * - Outgoing mentions
 *
 * At some point in time:
 * - post videos
 * - direct messages
 * - Sending Quote shares https://atproto.com/lexicons/app-bsky-embed#appbskyembedrecord and https://atproto.com/lexicons/app-bsky-embed#appbskyembedrecordwithmedia
 *
 * Possibly not possible:
 * - only fetch new posts
 *
 * Currently not possible, due to limitations in Friendica
 * - mute contacts https://atproto.com/lexicons/app-bsky-graph#appbskygraphmuteactor
 * - unmute contacts https://atproto.com/lexicons/app-bsky-graph#appbskygraphunmuteactor
 *
 * Possibly interesting:
 * - https://atproto.com/lexicons/com-atproto-label#comatprotolabelsubscribelabels
 */

use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Cache\Enum\Duration;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Hook;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Conversation;
use Friendica\Model\Item;
use Friendica\Model\Photo;
use Friendica\Model\Post;
use Friendica\Model\User;
use Friendica\Object\Image;
use Friendica\Protocol\Activity;
use Friendica\Protocol\ATProtocol;
use Friendica\Protocol\Relay;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\ParseUrl;
use Friendica\Util\Strings;
use Friendica\Content\Post\Entity\PostMedia;

const BLUESKY_DEFAULT_POLL_INTERVAL = 10; // given in minutes
const BLUESKY_IMAGE_SIZE            = [1000000, 500000, 100000, 50000];

function bluesky_install()
{
	Hook::register('load_config', __FILE__, 'bluesky_load_config');
	Hook::register('hook_fork', __FILE__, 'bluesky_hook_fork');
	Hook::register('post_local', __FILE__, 'bluesky_post_local');
	Hook::register('notifier_normal', __FILE__, 'bluesky_send');
	Hook::register('jot_networks', __FILE__, 'bluesky_jot_nets');
	Hook::register('connector_settings', __FILE__, 'bluesky_settings');
	Hook::register('connector_settings_post', __FILE__, 'bluesky_settings_post');
	Hook::register('cron', __FILE__, 'bluesky_cron');
	Hook::register('support_follow', __FILE__, 'bluesky_support_follow');
	Hook::register('follow', __FILE__, 'bluesky_follow');
	Hook::register('unfollow', __FILE__, 'bluesky_unfollow');
	Hook::register('block', __FILE__, 'bluesky_block');
	Hook::register('unblock', __FILE__, 'bluesky_unblock');
	Hook::register('check_item_notification', __FILE__, 'bluesky_check_item_notification');
	Hook::register('item_by_link', __FILE__, 'bluesky_item_by_link');
}

function bluesky_load_config(ConfigFileManager $loader)
{
	DI::appHelper()->getConfigCache()->load($loader->loadAddonConfig('bluesky'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function bluesky_check_item_notification(array &$notification_data)
{
	if (empty($notification_data['uid'])) {
		return;
	}

	DI::atProtocol()->setApiForUser($notification_data['uid']);

	$did = DI::atProtocol()->getUserDid($notification_data['uid']);
	if (empty($did)) {
		return;
	}

	$notification_data['profiles'][] = $did;
}

function bluesky_item_by_link(array &$hookData)
{
	// Don't overwrite an existing result
	if (isset($hookData['item_id'])) {
		return;
	}

	DI::atProtocol()->setApiForUser($hookData['uid']);

	if (!str_starts_with($hookData['uri'], 'at://')) {
		$data = ParseUrl::getSiteinfoCached($hookData['uri']);
		$uri  = $data['atprotocol']['uri'] ?? '';
	} else {
		$uri = $hookData['uri'];
	}

	$uri = DI::atpProcessor()->fetchMissingPost($uri, $hookData['uid'], Item::PR_FETCHED, 0, 0, '', false, Conversation::PARCEL_CONNECTOR);
	DI::logger()->debug('Got post', ['uri' => $uri]);
	if (!empty($uri)) {
		$item = Post::selectFirst(['id'], ['uri' => $uri, 'uid' => $hookData['uid']]);
		if (!empty($item['id'])) {
			$hookData['item_id'] = $item['id'];
		}
	}
}

function bluesky_support_follow(array &$data)
{
	if ($data['protocol'] == Protocol::ATPROTO) {
		$data['result'] = true;
	}
}

function bluesky_follow(array &$hook_data)
{
	DI::atProtocol()->setApiForUser($hook_data['uid']);

	$token = DI::atProtocol()->getUserToken($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	DI::logger()->debug('Check if contact is AT Protocol', ['data' => $hook_data]);
	$contact = DBA::selectFirst('contact', [], ['network' => Protocol::ATPROTO, 'nurl' => Strings::normaliseLink($hook_data['url']), 'uid' => [0, $hook_data['uid']]]);
	if (empty($contact)) {
		return;
	}

	$record = [
		'subject'   => $contact['url'],
		'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
		'$type'     => 'app.bsky.graph.follow',
	];

	$activity = DI::atProtocol()->createRecord($hook_data['uid'], 'app.bsky.graph.follow', $record);
	if (!empty($activity->uri)) {
		$hook_data['contact'] = $contact;
		DI::logger()->debug('Successfully start following', ['url' => $contact['url'], 'uri' => $activity->uri]);
	}
}

function bluesky_unfollow(array &$hook_data)
{
	DI::atProtocol()->setApiForUser($hook_data['uid']);

	$token = DI::atProtocol()->getUserToken($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	if ($hook_data['contact']['network'] != Protocol::ATPROTO) {
		return;
	}

	$data = DI::atProtocol()->XRPCGet('app.bsky.actor.getProfile', ['actor' => $hook_data['contact']['url']], $hook_data['uid']);
	if (empty($data->viewer) || empty($data->viewer->following)) {
		return;
	}

	DI::atProtocol()->deleteRecord($hook_data['uid'], $data->viewer->following);

	$hook_data['result'] = true;
}

function bluesky_block(array &$hook_data)
{
	DI::atProtocol()->setApiForUser($hook_data['uid']);

	$token = DI::atProtocol()->getUserToken($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	if ($hook_data['contact']['network'] != Protocol::ATPROTO) {
		return;
	}

	$record = [
		'subject'   => $hook_data['contact']['url'],
		'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
		'$type'     => 'app.bsky.graph.block',
	];

	$activity = DI::atProtocol()->createRecord($hook_data['uid'], 'app.bsky.graph.block', $record);
	if (!empty($activity->uri)) {
		$ucid = Contact::getUserContactId($hook_data['contact']['id'], $hook_data['uid']);
		if ($ucid) {
			Contact::remove($ucid);
		}
		DI::logger()->debug('Successfully blocked contact', ['url' => $hook_data['contact']['url'], 'uri' => $activity->uri]);
	}
}

function bluesky_unblock(array &$hook_data)
{
	DI::atProtocol()->setApiForUser($hook_data['uid']);

	$token = DI::atProtocol()->getUserToken($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	if ($hook_data['contact']['network'] != Protocol::ATPROTO) {
		return;
	}

	$data = DI::atProtocol()->XRPCGet('app.bsky.actor.getProfile', ['actor' => $hook_data['contact']['url']], $hook_data['uid']);
	if (empty($data->viewer) || empty($data->viewer->blocking)) {
		return;
	}

	DI::atProtocol()->deleteRecord($hook_data['uid'], $data->viewer->blocking);

	$hook_data['result'] = true;
}

function bluesky_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/bluesky/');

	$o = Renderer::replaceMacros($t, [
		'$submit'            => DI::l10n()->t('Save Settings'),
		'$friendica_handles' => ['friendica_handles', DI::l10n()->t('Allow your users to use your hostname for their Atmosphere handles'), DI::config()->get('bluesky', 'friendica_handles'), DI::l10n()->t('Before enabling this option, you have to setup a wildcard domain configuration and you have to enable wildcard requests in your webserver configuration. On Apache this is done by adding "ServerAlias *.%s" to your HTTP configuration. You don\'t need to change the HTTPS configuration.', DI::baseUrl()->getHost())],
	]);
}

function bluesky_addon_admin_post()
{
	DI::config()->set('bluesky', 'friendica_handles', (bool) $_POST['friendica_handles']);
}

function bluesky_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	DI::atProtocol()->setApiForUser(DI::userSession()->getLocalUserId());

	$enabled          = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post')            ?? false;
	$def_enabled      = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default') ?? false;
	$pds              = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'pds');
	$handle           = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'handle');
	$web              = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'web');
	$did              = DI::atProtocol()->getUserDid(DI::userSession()->getLocalUserId());
	$token            = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'access_token');
	$import           = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'import')           ?? false;
	$import_feeds     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'import_feeds')     ?? false;
	$complete_threads = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'complete_threads') ?? false;
	$custom_handle    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'friendica_handle') ?? false;

	if (DI::config()->get('bluesky', 'friendica_handles')) {
		$self             = User::getById(DI::userSession()->getLocalUserId(), ['nickname']);
		$host_handle      = $self['nickname'] . '.' . DI::baseUrl()->getHost();
		$friendica_handle = ['bluesky_friendica_handle', DI::l10n()->t('Allow to use %s as your Atmosphere handle.', $host_handle), $custom_handle, DI::l10n()->t('When enabled, you can use %s as your Atmosphere handle. After you enabled this option, please go to https://bsky.app/settings and select to change your handle. Select that you have got your own domain. Then enter %s and select "No DNS Panel". Then select "Verify Text File".', $host_handle, $host_handle)];
		if ($custom_handle) {
			$handle = $host_handle;
		}
	} else {
		$friendica_handle = [];
	}

	$web_frontend = ['' => 'System Default'];
	foreach (DI::config()->get('atprotocol', 'frontends') as $key => $frontend) {
		$web_frontend[$key] = $frontend[0];
	}

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/bluesky/');
	$html = Renderer::replaceMacros($t, [
		'$enable'           => ['bluesky', DI::l10n()->t('Enable Atmosphere Addon'), $enabled],
		'$bydefault'        => ['bluesky_bydefault', DI::l10n()->t('Post to the Atmosphere by default'), $def_enabled],
		'$import'           => ['bluesky_import', DI::l10n()->t('Import the remote timeline'), $import],
		'$import_feeds'     => ['bluesky_import_feeds', DI::l10n()->t('Import the pinned feeds'), $import_feeds, DI::l10n()->t('When activated, Posts will be imported from all the feeds that you pinned in your Atmosphere client.')],
		'$complete_threads' => ['bluesky_complete_threads', DI::l10n()->t('Complete the threads'), $complete_threads, DI::l10n()->t('When activated, the system fetches additional replies for the posts in the timeline. This leads to more complete threads.')],
		'$custom_handle'    => $friendica_handle,
		'$pds'              => ['bluesky_pds', DI::l10n()->t('Personal Data Server'), $pds, DI::l10n()->t('The personal data server (PDS) is the system that hosts your profile.'), '', 'readonly'],
		'$handle'           => ['bluesky_handle', DI::l10n()->t('Atmosphere handle'), $handle, '', '', $custom_handle ? 'readonly' : ''],
		'$did'              => ['bluesky_did', DI::l10n()->t('Atmosphere DID'), $did, DI::l10n()->t('This is the unique identifier. It will be fetched automatically, when the handle is entered.'), '', 'readonly'],
		'$password'         => ['bluesky_password', DI::l10n()->t('Atmosphere app password'), '', DI::l10n()->t("Please don't add your real password here, but instead create a specific app password in the settings of your Atmosphere client.")],
		'$web'              => ['bluesky_web', DI::l10n()->t('Web front end'), $web, DI::l10n()->t('Choose your preferred external web front end for displaying posts and profiles.'), $web_frontend, ''],
		'$status'           => bluesky_get_status($handle, $did, $pds, $token),
	]);

	$data = [
		'connector' => 'bluesky',
		'title'     => DI::l10n()->t('Atmosphere (Bluesky, Eurosky, Blacksky, ...) Import/Export'),
		'image'     => 'images/500px-AT_Protocol_logo.png',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}

function bluesky_get_status(string $handle = null, string $did = null, string $pds = null, string $token = null): string
{
	if (empty($handle)) {
		return DI::l10n()->t('You are not authenticated. Please enter your handle and the app password.');
	}

	$status  = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'status')         ?? ATProtocol::STATUS_UNKNOWN;
	$message = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'status-message') ?? '';

	// Fallback mechanism for connection that had been established before the introduction of the status
	if ($status == ATProtocol::STATUS_UNKNOWN) {
		if (empty($did)) {
			$status = ATProtocol::STATUS_DID_FAIL;
		} elseif (empty($pds)) {
			$status = ATProtocol::STATUS_PDS_FAIL;
		} elseif (!empty($token)) {
			$status = ATProtocol::STATUS_TOKEN_OK;
		} else {
			$status = ATProtocol::STATUS_TOKEN_FAIL;
		}
	}

	switch ($status) {
		case ATProtocol::STATUS_TOKEN_OK:
			return DI::l10n()->t("You are authenticated to the Atmosphere PDS. For security reasons the password isn't stored.");
		case ATProtocol::STATUS_SUCCESS:
			return DI::l10n()->t('The communication with the personal data server service (PDS) is established.');
		case ATProtocol::STATUS_API_FAIL:
			return DI::l10n()->t('Communication issues with the personal data server service (PDS): %s', $message);
		case ATProtocol::STATUS_DID_FAIL:
			return DI::l10n()->t('The DID for the provided handle could not be detected. Please check if you entered the correct handle.');
		case ATProtocol::STATUS_PDS_FAIL:
			return DI::l10n()->t('The personal data server service (PDS) could not be detected.');
		case ATProtocol::STATUS_TOKEN_FAIL:
			return DI::l10n()->t('The authentication with the provided handle and password failed. Please check if you entered the correct password.');
		default:
			return '';
	}
}

function bluesky_settings_post(array &$b)
{
	if (empty($_POST['bluesky-submit'])) {
		return;
	}

	DI::atProtocol()->setApiForUser(DI::userSession()->getLocalUserId());

	$old_pds    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'pds');
	$old_handle = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'handle');
	$old_did    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'did');

	$handle = trim($_POST['bluesky_handle'], ' @');

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'post', intval($_POST['bluesky']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default', intval($_POST['bluesky_bydefault']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'handle', $handle);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'import', intval($_POST['bluesky_import']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'import_feeds', intval($_POST['bluesky_import_feeds']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'complete_threads', intval($_POST['bluesky_complete_threads']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'friendica_handle', intval($_POST['bluesky_friendica_handle'] ?? false));
	if ($_POST['bluesky_web'] <> '') {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'web', $_POST['bluesky_web']);
	} else {
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'web');
	}

	if (!empty($handle)) {
		$did = DI::atProtocol()->getUserDid(DI::userSession()->getLocalUserId(), empty($old_did) || $old_handle != $handle);
		if (!empty($did) && (empty($old_pds) || $old_handle != $handle)) {
			$pds = DI::atProtocol()->getPdsOfDid($did);
			if (empty($pds)) {
				DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'status', ATProtocol::STATUS_PDS_FAIL);
			}
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'pds', $pds);
		} else {
			$pds = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'pds');
		}
	} else {
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'did');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'pds');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'password');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'access_token');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'refresh_token');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'token_created');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'status');
	}

	if (!empty($did) && !empty($pds) && !empty($_POST['bluesky_password'])) {
		DI::atProtocol()->createUserToken(DI::userSession()->getLocalUserId(), $_POST['bluesky_password']);
	}
}

function bluesky_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post')) {
		$jotnets_fields[] = [
			'type'  => 'checkbox',
			'field' => [
				'bluesky_enable',
				DI::l10n()->t('Post via the Atmosphere'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default'),
			],
		];
	}
}

function bluesky_cron()
{
	$last = (int) DI::keyValue()->get('bluesky_last_poll');

	$poll_interval = intval(DI::config()->get('bluesky', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = BLUESKY_DEFAULT_POLL_INTERVAL;
	}

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			DI::logger()->notice('poll interval not reached');
			return;
		}
	}
	DI::logger()->notice('cron_start');

	$abandon_days = intval(DI::config()->get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$pconfigs = DBA::selectToArray('pconfig', [], ["`cat` = ? AND `k` IN (?, ?) AND `v`", 'bluesky', 'import', 'import_feeds']);
	foreach ($pconfigs as $pconfig) {
		DI::atProtocol()->setApiForUser($pconfig['uid']);

		if (empty(DI::atProtocol()->getUserDid($pconfig['uid']))) {
			DI::logger()->debug('User has got no valid DID', ['uid' => $pconfig['uid']]);
			continue;
		}

		if ($abandon_days != 0) {
			if (!DBA::exists('user', ["`uid` = ? AND `login_date` >= ?", $pconfig['uid'], $abandon_limit])) {
				DI::logger()->notice('abandoned account: timeline from user will not be imported', ['user' => $pconfig['uid']]);
				continue;
			}
		}

		// Refresh the token now, so that it doesn't need to be refreshed in parallel by the following workers
		DI::logger()->debug('Refresh the token', ['uid' => $pconfig['uid']]);
		DI::atProtocol()->getUserToken($pconfig['uid']);

		$last_sync = DI::pConfig()->get($pconfig['uid'], 'bluesky', 'last_contact_sync');
		if ($last_sync < (time() - 86400)) {
			DI::atpActor()->syncContacts($pconfig['uid']);
			DI::pConfig()->set($pconfig['uid'], 'bluesky', 'last_contact_sync', time());
		}

		Worker::add(['priority' => Worker::PRIORITY_MEDIUM, 'force_priority' => true], 'addon/bluesky/bluesky_notifications.php', $pconfig['uid']);
		if (DI::pConfig()->get($pconfig['uid'], 'bluesky', 'import')) {
			Worker::add(['priority' => Worker::PRIORITY_MEDIUM, 'force_priority' => true], 'addon/bluesky/bluesky_timeline.php', $pconfig['uid']);
		}
		if (DI::pConfig()->get($pconfig['uid'], 'bluesky', 'import_feeds')) {
			DI::logger()->debug('Fetch feeds for user', ['uid' => $pconfig['uid']]);
			$feeds = bluesky_get_feeds($pconfig['uid']);
			foreach ($feeds as $feed) {
				Worker::add(['priority' => Worker::PRIORITY_MEDIUM, 'force_priority' => true], 'addon/bluesky/bluesky_feed.php', $pconfig['uid'], $feed);
			}
		}
		DI::logger()->debug('Polling done for user', ['uid' => $pconfig['uid']]);
	}

	DI::logger()->notice('Polling done for all users');

	DI::keyValue()->set('bluesky_last_poll', time());

	$last_clean = DI::keyValue()->get('bluesky_last_clean');
	if (empty($last_clean) || ($last_clean + 86400 < time())) {
		DI::logger()->notice('Start contact cleanup');
		$contacts = DBA::select('account-user-view', ['id', 'pid'], ["`network` = ? AND `uid` != ? AND `rel` = ?", Protocol::ATPROTO, 0, Contact::NOTHING]);
		while ($contact = DBA::fetch($contacts)) {
			Worker::add(Worker::PRIORITY_LOW, 'MergeContact', $contact['pid'], $contact['id'], 0);
		}
		DBA::close($contacts);
		DI::keyValue()->set('bluesky_last_clean', time());
		DI::logger()->notice('Contact cleanup done');
	}

	DI::logger()->notice('cron_end');
}

function bluesky_hook_fork(array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if (($post['created'] !== $post['edited']) && !$post['deleted']) {
		DI::logger()->info('Editing is not supported by the addon');
		$b['execute'] = false;
		return;
	}

	if (DI::pConfig()->get($post['uid'], 'bluesky', 'import')) {
		// Don't post if it isn't a reply to an Atmosphere post
		if (($post['gravity'] != Item::GRAVITY_PARENT) && !Post::exists(['id' => $post['parent'], 'network' => Protocol::ATPROTO])) {
			DI::logger()->notice('No Atmosphere parent found', ['item' => $post['id']]);
			$b['execute'] = false;
			return;
		}
	} elseif (!strstr($post['postopts'] ?? '', 'bluesky') || ($post['gravity'] != Item::GRAVITY_PARENT) || ($post['private'] == Item::PRIVATE)) {
		DI::logger()->info('Post will not be exported', ['uid' => $post['uid'], 'postopts' => $post['postopts'], 'gravity' => $post['gravity'], 'private' => $post['private']]);
		$b['execute'] = false;
		return;
	}
}

function bluesky_post_local(array &$b)
{
	if (!DI::userSession()->getLocalUserId() || (DI::userSession()->getLocalUserId() != $b['uid'])) {
		return;
	}

	if ($b['edit'] || ($b['private'] == Item::PRIVATE) || ($b['gravity'] != Item::GRAVITY_PARENT)) {
		return;
	}

	$bluesky_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post'));
	$bluesky_enable = (($bluesky_post && !empty($_REQUEST['bluesky_enable'])) ? intval($_REQUEST['bluesky_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default'))) {
		$bluesky_enable = 1;
	}

	if (!$bluesky_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'bluesky';
}

function bluesky_send(array &$b)
{
	DI::atProtocol()->setApiForUser($b['uid']);

	if (($b['created'] !== $b['edited']) && !$b['deleted']) {
		return;
	}

	if (Item::isGroupPost($b['uri-id'])) {
		return;
	}

	if ($b['gravity'] != Item::GRAVITY_PARENT) {
		DI::logger()->debug('Got comment', ['item' => $b]);

		if ($b['deleted']) {
			$uri = DI::atpProcessor()->getUriClass($b['uri']);
			if (empty($uri)) {
				DI::logger()->debug('Not an Atmosphere post', ['uri' => $b['uri']]);
				return;
			}
			DI::atProtocol()->deleteRecord($b['uid'], $uri->uri);
			return;
		}

		$root   = DI::atpProcessor()->getUriClass($b['parent-uri']);
		$parent = DI::atpProcessor()->getUriClass($b['thr-parent']);

		if (empty($root) || empty($parent)) {
			DI::logger()->debug('No Atmosphere post', ['parent' => $b['parent'], 'thr-parent' => $b['thr-parent']]);
			return;
		}

		if ($b['gravity'] == Item::GRAVITY_COMMENT) {
			DI::logger()->debug('Posting comment', ['root' => $root, 'parent' => $parent]);
			bluesky_create_post($b, $root, $parent);
			return;
		} elseif (in_array($b['verb'], [Activity::LIKE, Activity::ANNOUNCE])) {
			bluesky_create_activity($b, $parent);
		}
		return;
	} elseif (($b['private'] == Item::PRIVATE) || !strstr($b['postopts'], 'bluesky')) {
		return;
	}

	bluesky_create_post($b);
}

function bluesky_create_activity(array $item, ?stdClass $parent = null)
{
	$uid = $item['uid'];
	DI::atProtocol()->setApiForUser($uid);

	$token = DI::atProtocol()->getUserToken($uid);
	if (empty($token)) {
		return;
	}

	if ($item['verb'] == Activity::LIKE) {
		$collection = 'app.bsky.feed.like';
	} elseif ($item['verb'] == Activity::ANNOUNCE) {
		$collection = 'app.bsky.feed.repost';
	} else {
		return;
	}

	$record = [
		'subject'   => $parent,
		'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
		'$type'     => $collection,
	];

	$activity = DI::atProtocol()->createRecord($uid, $collection, $record);
	if (empty($activity->uri)) {
		return;
	}
	DI::logger()->debug('Activity done', ['return' => $activity]);
	$uri = DI::atpProcessor()->getUri($activity);
	Item::update(['extid' => $uri], ['guid' => $item['guid']]);
	DI::logger()->debug('Set extid', ['id' => $item['id'], 'extid' => $activity]);
}

function bluesky_create_post(array $item, stdClass $root = null, stdClass $parent = null)
{
	$uid = $item['uid'];
	DI::atProtocol()->setApiForUser($uid);

	$token = DI::atProtocol()->getUserToken($uid);
	if (empty($token)) {
		return;
	}

	// Try to fetch the language from the post itself
	if (!empty($item['language'])) {
		$language = array_key_first(json_decode($item['language'], true));
	} else {
		$language = '';
	}

	$item['body'] = Post\Media::removeFromBody($item['body']);

	foreach (Post\Media::getByURIId($item['uri-id'], [PostMedia::TYPE_AUDIO, PostMedia::TYPE_VIDEO, PostMedia::TYPE_ACTIVITY]) as $media) {
		if (strpos($item['body'], $media['url']) === false) {
			$item['body'] .= "\n[url]" . $media['url'] . "[/url]\n";
		}
	}

	if (!empty($item['quote-uri-id'])) {
		$quote = Post::selectFirstPost(['uri', 'plink'], ['uri-id' => $item['quote-uri-id']]);
		if (!empty($quote)) {
			if ((strpos($item['body'], $quote['plink'] ?: $quote['uri']) === false) && (strpos($item['body'], $quote['uri']) === false)) {
				$item['body'] .= "\n[url]" . ($quote['plink'] ?: $quote['uri']) . "[/url]\n";
			}
		}
	}

	$item['body'] = bluesky_set_mentions($item['body']);

	$urls         = bluesky_get_urls($item['body']);
	$item['body'] = $urls['body'];

	$msg = Plaintext::getPost($item, 300, false, BBCode::ATPROTOCOL);
	foreach ($msg['parts'] as $key => $part) {

		$facets = bluesky_get_facets($part, $urls['urls']);

		$record = [
			'text'      => $facets['body'],
			'$type'     => 'app.bsky.feed.post',
			'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
		];

		if (!empty($language)) {
			$record['langs'] = [$language];
		}

		if (!empty($facets['facets'])) {
			$record['facets'] = $facets['facets'];
		}

		if (!empty($root)) {
			$record['reply'] = ['root' => $root, 'parent' => $parent];
		}

		if ($key == count($msg['parts']) - 1) {
			$record = bluesky_add_embed($uid, $msg, $record);
			if (empty($record)) {
				if (Worker::getRetrial() < 3) {
					Worker::defer();
				}
				return;
			}
		}

		$parent = DI::atProtocol()->createRecord($uid, 'app.bsky.feed.post', $record);
		if (empty($parent->uri)) {
			if ($part == 0) {
				Worker::defer();
			}
			return;
		}
		DI::logger()->debug('Posting done', ['return' => $parent]);
		if (empty($root)) {
			$root = $parent;
		}
		if (($key == 0) && ($item['gravity'] != Item::GRAVITY_PARENT)) {
			$uri = DI::atpProcessor()->getUri($parent);
			Item::update(['extid' => $uri], ['guid' => $item['guid']]);
			DI::logger()->debug('Set extid', ['id' => $item['id'], 'extid' => $uri]);
		}
	}
}

function bluesky_set_mentions(string $body): string
{
	// Remove all url based mention links
	$body = preg_replace("/([@!])\[url\=(.*?)\](.*?)\[\/url\]/ism", '$1$3', $body);

	if (!preg_match_all("/[@!]\[url\=(did:.*?)\](.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER)) {
		return $body;
	}

	foreach ($matches as $match) {
		$contact = Contact::selectFirst(['addr'], ['nurl' => $match[1]]);
		if (!empty($contact['addr'])) {
			$body = str_replace($match[0], '@[url=' . $match[1] . ']' . $contact['addr'] . '[/url]', $body);
		} else {
			$body = str_replace($match[0], '@' . $match[2], $body);
		}
	}

	return $body;
}

function bluesky_get_urls(string $body): array
{
	$body = BBCode::expandVideoLinks($body);
	$urls = [];

	// Search for Mentions
	if (preg_match_all("/[@!]\[url\=(did:.*?)\](.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$text                           = '@' . $match[2];
			$urls[strpos($body, $match[0])] = ['mention' => $match[1], 'text' => $text, 'hash' => $text];
			$body                           = str_replace($match[0], $text, $body);
		}
	}

	// Search for hash tags
	if (preg_match_all("/#\[url\=(https?:.*?)\](.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$text                           = '#' . $match[2];
			$urls[strpos($body, $match[0])] = ['tag' => $match[2], 'text' => $text, 'hash' => $text];
			$body                           = str_replace($match[0], $text, $body);
		}
	}

	// Search for pure links
	if (preg_match_all("/\[url\](https?:.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$text                           = Strings::getStyledURL($match[1]);
			$hash                           = bluesky_get_hash_for_url($match[0], mb_strlen($text));
			$urls[strpos($body, $match[0])] = ['url' => $match[1], 'text' => $text, 'hash' => $hash];
			$body                           = str_replace($match[0], $hash, $body);
		}
	}

	// Search for links with descriptions
	if (preg_match_all("/\[url\=(https?:.*?)\](.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			if ($match[1] == $match[2]) {
				$text = Strings::getStyledURL($match[1]);
			} else {
				$text = $match[2];
			}
			if (mb_strlen($text) < 100) {
				$hash                           = bluesky_get_hash_for_url($match[0], mb_strlen($text));
				$urls[strpos($body, $match[0])] = ['url' => $match[1], 'text' => $text, 'hash' => $hash];
				$body                           = str_replace($match[0], $hash, $body);
			} else {
				$text                           = Strings::getStyledURL($match[1]);
				$hash                           = bluesky_get_hash_for_url($match[0], mb_strlen($text));
				$urls[strpos($body, $match[0])] = ['url' => $match[1], 'text' => $text, 'hash' => $hash];
				$body                           = str_replace($match[0], $text . ' ' . $hash, $body);
			}
		}
	}

	asort($urls);

	return ['body' => $body, 'urls' => $urls];
}

function bluesky_get_hash_for_url(string $text, int $linklength): string
{
	if ($linklength <= 10) {
		return '|' . hash('crc32', $text) . '|';
	}
	return substr('|' . hash('crc32', $text) . base64_encode($text), 0, $linklength - 2) . '|';
}

function bluesky_get_facets(string $body, array $urls): array
{
	$facets = [];

	foreach ($urls as $url) {
		$pos = strpos($body, $url['hash']);
		if ($pos === false) {
			continue;
		}
		if ($pos > 0) {
			$prefix = substr($body, 0, $pos);
		} else {
			$prefix = '';
		}

		$body = $prefix . $url['text'] . substr($body, $pos + strlen($url['hash']));

		$facet                   = new stdClass();
		$facet->index            = new stdClass();
		$facet->index->byteEnd   = $pos + strlen($url['text']);
		$facet->index->byteStart = $pos;

		$feature = new stdClass();

		$type = '$type';
		if (!empty($url['tag'])) {
			$feature->tag   = $url['tag'];
			$feature->$type = 'app.bsky.richtext.facet#tag';
		} elseif (!empty($url['url'])) {
			$feature->uri   = $url['url'];
			$feature->$type = 'app.bsky.richtext.facet#link';
		} elseif (!empty($url['mention'])) {
			$feature->did   = $url['mention'];
			$feature->$type = 'app.bsky.richtext.facet#mention';
		} else {
			continue;
		}

		$facet->features = [$feature];
		$facets[]        = $facet;
	}

	return ['facets' => $facets, 'body' => $body];
}

function bluesky_add_embed(int $uid, array $msg, array $record): array
{
	if (($msg['type'] != 'link') && !empty($msg['images'])) {
		$images = [];
		foreach ($msg['images'] as $image) {
			if (count($images) == 4) {
				continue;
			}
			$photo = Photo::selectFirst([], ['id' => $image['id']]);
			$blob  = bluesky_upload_blob($uid, $photo);
			if (empty($blob)) {
				return [];
			}
			$images[] = [
				'alt'         => $image['description'] ?? '',
				'image'       => $blob,
				'aspectRatio' => [
					'width'  => $photo['width'],
					'height' => $photo['height'],
				],
			];
		}
		if (!empty($images)) {
			$record['embed'] = ['$type' => 'app.bsky.embed.images', 'images' => $images];
		}
	} elseif ($msg['type'] == 'link') {
		$record['embed'] = [
			'$type'    => 'app.bsky.embed.external',
			'external' => [
				'uri'         => $msg['url'],
				'title'       => $msg['title']       ?? '',
				'description' => $msg['description'] ?? '',
			],
		];
		if (!empty($msg['image'])) {
			$photo = Photo::createPhotoForExternalResource($msg['image']);
			$blob  = bluesky_upload_blob($uid, $photo);
			if (!empty($blob)) {
				$record['embed']['external']['thumb'] = $blob;
			}
		}
	}
	return $record;
}

function bluesky_upload_blob(int $uid, array $photo): ?stdClass
{
	$retrial = Worker::getRetrial();
	$content = Photo::getImageForPhoto($photo);

	$picture = new Image($content, $photo['type'], $photo['filename']);
	$height  = $picture->getHeight();
	$width   = $picture->getWidth();
	$size    = strlen($content);

	$picture    = Photo::resizeToFileSize($picture, BLUESKY_IMAGE_SIZE[$retrial]);
	$new_height = $picture->getHeight();
	$new_width  = $picture->getWidth();
	$content    = (string) $picture->asString();
	$new_size   = strlen($content);

	if (($size != 0) && ($new_size == 0) && ($retrial == 0)) {
		DI::logger()->warning('Size is empty after resize, uploading original file', ['uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size]);
		$content = Photo::getImageForPhoto($photo);
	} else {
		DI::logger()->info('Uploading', ['uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size]);
	}

	$data = DI::atProtocol()->post($uid, '/xrpc/com.atproto.repo.uploadBlob', $content, ['Content-type' => $photo['type'], 'Authorization' => ['Bearer ' . DI::atProtocol()->getUserToken($uid)]]);
	if (empty($data) || empty($data->blob)) {
		DI::logger()->info('Uploading failed', ['uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size]);
		return null;
	}

	Item::incrementOutbound(Protocol::ATPROTO);
	DI::logger()->debug('Uploaded blob', ['return' => $data, 'uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size]);
	return $data->blob;
}

function bluesky_fetch_timeline(int $uid)
{
	DI::atProtocol()->setApiForUser($uid);

	$data = DI::atProtocol()->XRPCGet('app.bsky.feed.getTimeline', [], $uid);
	if (empty($data)) {
		return;
	}

	if (empty($data->feed)) {
		return;
	}

	foreach (array_reverse($data->feed) as $entry) {
		$causer = DI::atpActor()->getContactByDID($entry->post->author->did, $uid, 0, true);
		if (!empty($entry->reply)) {
			if (!empty($entry->reply->root)) {
				bluesky_complete_post($entry->reply->root, $uid, Item::PR_COMMENT, $causer['id'], Conversation::PARCEL_CONNECTOR);
			}
			if (!empty($entry->reply->parent)) {
				bluesky_complete_post($entry->reply->parent, $uid, Item::PR_COMMENT, $causer['id'], Conversation::PARCEL_CONNECTOR);
			}
		}
		DI::atpProcessor()->processPost($entry->post, $uid, Item::PR_NONE, 0, 0, Conversation::PARCEL_CONNECTOR);
		if (!empty($entry->reason)) {
			bluesky_process_reason($entry->reason, DI::atpProcessor()->getUri($entry->post), $uid);
		}
	}
}

function bluesky_complete_post(stdClass $post, int $uid, int $post_reason, int $causer, int $protocol): int
{
	$complete     = DI::pConfig()->get($uid, 'bluesky', 'complete_threads');
	$existing_uri = DI::atpProcessor()->getPostUri(DI::atpProcessor()->getUri($post), $uid);
	if (!empty($existing_uri)) {
		$comments = Post::countPosts(['thr-parent' => $existing_uri, 'gravity' => Item::GRAVITY_COMMENT]);
		if (($post->replyCount <= $comments) || !$complete) {
			return DI::atpProcessor()->fetchUriId($existing_uri, $uid);
		}
	}

	if ($complete) {
		$uri    = DI::atpProcessor()->fetchMissingPost(DI::atpProcessor()->getUri($post), $uid, $post_reason, $causer, 0, '', false, $protocol);
		$uri_id = DI::atpProcessor()->fetchUriId($uri, $uid);
	} else {
		$uri_id = DI::atpProcessor()->processPost($post, $uid, $post_reason, $causer, 0, $protocol);
	}
	return $uri_id;
}

function bluesky_process_reason(stdClass $reason, string $uri, int $uid)
{
	$type = '$type';
	if ($reason->$type != 'app.bsky.feed.defs#reasonRepost') {
		return;
	}

	$contact = DI::atpActor()->getContactByDID($reason->by->did, $uid, 0);

	$item = [
		'network'       => Protocol::ATPROTO,
		'protocol'      => Conversation::PARCEL_CONNECTOR,
		'uid'           => $uid,
		'wall'          => false,
		'uri'           => $reason->by->did . '/app.bsky.feed.repost/' . $reason->indexedAt,
		'private'       => Item::UNLISTED,
		'contact-id'    => $contact['id'],
		'author-name'   => $contact['name'],
		'author-link'   => $contact['url'],
		'author-avatar' => $contact['avatar'],
		'verb'          => Activity::ANNOUNCE,
		'body'          => Activity::ANNOUNCE,
		'gravity'       => Item::GRAVITY_ACTIVITY,
		'object-type'   => Activity\ObjectType::NOTE,
		'thr-parent'    => $uri,
	];

	if (Post::exists(['uri' => $item['uri'], 'uid' => $uid])) {
		return;
	}

	if (Post::exists(['uid' => $item['uid'], 'thr-parent' => $item['thr-parent'], 'verb' => $item['verb'], 'contact-id' => $item['contact-id']])) {
		return;
	}

	$item['guid']         = DI::postUriGenerator()->guidFromUri($item['uri'], $contact['alias']);
	$item['owner-name']   = $item['author-name'];
	$item['owner-link']   = $item['author-link'];
	$item['owner-avatar'] = $item['author-avatar'];
	if (Item::insert($item)) {
		$pcid = Contact::getPublicContactId($contact['id'], $uid);
		Item::update(['post-reason' => Item::PR_ANNOUNCEMENT, 'causer-id' => $pcid], ['uri' => $uri, 'uid' => $uid]);
	}
}

function bluesky_fetch_notifications(int $uid)
{
	DI::atProtocol()->setApiForUser($uid);

	$data = DI::atProtocol()->XRPCGet('app.bsky.notification.listNotifications', [], $uid);
	if (empty($data->notifications)) {
		return;
	}

	foreach ($data->notifications as $notification) {
		if (DI::contentItem()->isTooOld($notification->indexedAt)) {
			continue;
		}

		$uri = DI::atpProcessor()->getUri($notification);
		if (Post::exists(['uri' => $uri, 'uid' => $uid]) || Post::exists(['extid' => $uri, 'uid' => $uid])) {
			DI::logger()->debug('Notification already processed', ['uid' => $uid, 'reason' => $notification->reason, 'uri' => $uri, 'indexedAt' => $notification->indexedAt]);
			continue;
		}
		DI::logger()->debug('Process notification', ['uid' => $uid, 'reason' => $notification->reason, 'uri' => $uri, 'indexedAt' => $notification->indexedAt]);
		switch ($notification->reason) {
			case 'like':
				$item               = DI::atpProcessor()->getHeaderFromPost($notification, $uri, $uid, Conversation::PARCEL_CONNECTOR);
				$item['gravity']    = Item::GRAVITY_ACTIVITY;
				$item['body']       = $item['verb'] = Activity::LIKE;
				$item['thr-parent'] = DI::atpProcessor()->getUri($notification->record->subject);
				$item['thr-parent'] = DI::atpProcessor()->fetchMissingPost($item['thr-parent'], $uid, Item::PR_FETCHED, $item['contact-id'], 0, '', false, Conversation::PARCEL_CONNECTOR);
				if (!empty($item['thr-parent'])) {
					$data = Item::insert($item);
					DI::logger()->debug('Got like', ['uid' => $uid, 'result' => $data, 'uri' => $uri]);
				} else {
					DI::logger()->info('Thread parent not found', ['uid' => $uid, 'parent' => $item['thr-parent'], 'uri' => $uri]);
				}
				break;

			case 'repost':
				$item               = DI::atpProcessor()->getHeaderFromPost($notification, $uri, $uid, Conversation::PARCEL_CONNECTOR);
				$item['gravity']    = Item::GRAVITY_ACTIVITY;
				$item['body']       = $item['verb'] = Activity::ANNOUNCE;
				$item['thr-parent'] = DI::atpProcessor()->getUri($notification->record->subject);
				$item['thr-parent'] = DI::atpProcessor()->fetchMissingPost($item['thr-parent'], $uid, Item::PR_FETCHED, $item['contact-id'], 0, '', false, Conversation::PARCEL_CONNECTOR);
				if (!empty($item['thr-parent'])) {
					$data = Item::insert($item);
					DI::logger()->debug('Got repost', ['uid' => $uid, 'result' => $data, 'uri' => $uri]);
				} else {
					DI::logger()->info('Thread parent not found', ['uid' => $uid, 'parent' => $item['thr-parent'], 'uri' => $uri]);
				}
				break;

			case 'follow':
				$contact = DI::atpActor()->getContactByDID($notification->author->did, $uid, $uid);
				DI::logger()->debug('New follower', ['uid' => $uid, 'nick' => $contact['nick'], 'uri' => $uri]);
				break;

			case 'mention':
				$contact = DI::atpActor()->getContactByDID($notification->author->did, $uid, 0);
				$result  = DI::atpProcessor()->fetchMissingPost($uri, $uid, Item::PR_TO, $contact['id'], 0, '', false, Conversation::PARCEL_CONNECTOR);
				DI::logger()->debug('Got mention', ['uid' => $uid, 'nick' => $contact['nick'], 'result' => $result, 'uri' => $uri]);
				break;

			case 'reply':
				$contact = DI::atpActor()->getContactByDID($notification->author->did, $uid, 0);
				$result  = DI::atpProcessor()->fetchMissingPost($uri, $uid, Item::PR_COMMENT, $contact['id'], 0, '', false, Conversation::PARCEL_CONNECTOR);
				DI::logger()->debug('Got reply', ['uid' => $uid, 'nick' => $contact['nick'], 'result' => $result, 'uri' => $uri]);
				break;

			case 'quote':
				$contact = DI::atpActor()->getContactByDID($notification->author->did, $uid, 0);
				$result  = DI::atpProcessor()->fetchMissingPost($uri, $uid, Item::PR_PUSHED, $contact['id'], 0, '', false, Conversation::PARCEL_CONNECTOR);
				DI::logger()->debug('Got quote', ['uid' => $uid, 'nick' => $contact['nick'], 'result' => $result, 'uri' => $uri]);
				break;

			default:
				DI::logger()->notice('Unhandled reason', ['reason' => $notification->reason, 'uri' => $uri]);
				break;
		}
	}
}

function bluesky_fetch_feed(int $uid, string $feed)
{
	DI::atProtocol()->setApiForUser($uid);

	$data = DI::atProtocol()->XRPCGet('app.bsky.feed.getFeed', ['feed' => $feed], $uid);
	if (empty($data)) {
		return;
	}

	if (empty($data->feed)) {
		return;
	}

	$feeddata = DI::atProtocol()->XRPCGet('app.bsky.feed.getFeedGenerator', ['feed' => $feed], $uid);
	if (!empty($feeddata) && !empty($feeddata->view)) {
		$feedurl  = $feeddata->view->uri;
		$feedname = $feeddata->view->displayName;
	} else {
		$feedurl  = $feed;
		$feedname = $feed;
	}

	foreach (array_reverse($data->feed) as $entry) {
		$contact   = DI::atpActor()->getContactByDID($entry->post->author->did, $uid, 0);
		$languages = $entry->post->record->langs ?? [];

		if (!Relay::isWantedLanguage($entry->post->record->text, 0, $contact['id'] ?? 0, $languages)) {
			DI::logger()->debug('Unwanted language detected', ['languages' => $languages, 'text' => $entry->post->record->text]);
			continue;
		}
		$causer = DI::atpActor()->getContactByDID($entry->post->author->did, $uid, 0);
		$uri_id = bluesky_complete_post($entry->post, $uid, Item::PR_TAG, $causer['id'], Conversation::PARCEL_CONNECTOR);
		if (!empty($uri_id)) {
			$stored = Post\Category::storeFileByURIId($uri_id, $uid, Post\Category::SUBCRIPTION, $feedname, $feedurl);
			DI::logger()->debug('Stored tag subscription for user', ['uri-id' => $uri_id, 'uid' => $uid, 'name' => $feedname, 'url' => $feedurl, 'stored' => $stored]);
		} else {
			DI::logger()->notice('Post not found', ['entry' => $entry]);
		}
		if (!empty($entry->reason)) {
			bluesky_process_reason($entry->reason, DI::atpProcessor()->getUri($entry->post), $uid);
		}
	}
}

function bluesky_get_feeds(int $uid): array
{
	$type        = '$type';
	$preferences = bluesky_get_preferences($uid);
	if (empty($preferences) || empty($preferences->preferences)) {
		return [];
	}
	foreach ($preferences->preferences as $preference) {
		if ($preference->$type == 'app.bsky.actor.defs#savedFeedsPrefV2') {
			$pinned = [];
			foreach ($preference->items as $item) {
				if (($item->type == 'feed') && $item->pinned) {
					$pinned[] = $item->value;
				}
			}
			return $pinned;
		}
	}
	return [];
}

function bluesky_get_preferences(int $uid): ?stdClass
{
	$cachekey = 'bluesky:preferences:' . $uid;
	$data     = DI::cache()->get($cachekey);
	if (!is_null($data)) {
		return $data;
	}

	$data = DI::atProtocol()->XRPCGet('app.bsky.actor.getPreferences', [], $uid);
	if (empty($data)) {
		return null;
	}

	DI::cache()->set($cachekey, $data, Duration::HOUR);
	return $data;
}
