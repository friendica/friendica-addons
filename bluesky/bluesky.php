<?php
/**
 * Name: Bluesky Connector
 * Description: Post to Bluesky
 * Version: 1.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 * @todo
 * Currently technical issues in the core:
 * - Outgoing mentions
 *
 * At some point in time:
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
use Friendica\Content\Text\HTML;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Cache\Enum\Duration;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Item;
use Friendica\Model\ItemURI;
use Friendica\Model\Photo;
use Friendica\Model\Post;
use Friendica\Network\HTTPClient\Client\HttpClientAccept;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;
use Friendica\Object\Image;
use Friendica\Protocol\Activity;
use Friendica\Protocol\Relay;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Strings;

const BLUESKY_DEFAULT_POLL_INTERVAL = 10; // given in minutes
const BLUESKY_HOST = 'https://bsky.app'; // Hard wired until Bluesky will run on multiple systems
const BLUESKY_IMAGE_SIZE = [1000000, 500000, 100000, 50000];

function bluesky_install()
{
	Hook::register('load_config',             __FILE__, 'bluesky_load_config');
	Hook::register('hook_fork',               __FILE__, 'bluesky_hook_fork');
	Hook::register('post_local',              __FILE__, 'bluesky_post_local');
	Hook::register('notifier_normal',         __FILE__, 'bluesky_send');
	Hook::register('jot_networks',            __FILE__, 'bluesky_jot_nets');
	Hook::register('connector_settings',      __FILE__, 'bluesky_settings');
	Hook::register('connector_settings_post', __FILE__, 'bluesky_settings_post');
	Hook::register('cron',                    __FILE__, 'bluesky_cron');
	Hook::register('support_follow',          __FILE__, 'bluesky_support_follow');
	Hook::register('support_probe',           __FILE__, 'bluesky_support_probe');
	Hook::register('follow',                  __FILE__, 'bluesky_follow');
	Hook::register('unfollow',                __FILE__, 'bluesky_unfollow');
	Hook::register('block',                   __FILE__, 'bluesky_block');
	Hook::register('unblock',                 __FILE__, 'bluesky_unblock');
	Hook::register('check_item_notification', __FILE__, 'bluesky_check_item_notification');
	Hook::register('probe_detect',            __FILE__, 'bluesky_probe_detect');
	Hook::register('item_by_link',            __FILE__, 'bluesky_item_by_link');
}

function bluesky_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('bluesky'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function bluesky_check_item_notification(array &$notification_data)
{
	$did = DI::pConfig()->get($notification_data['uid'], 'bluesky', 'did');

	if (!empty($did)) {
		$notification_data['profiles'][] = $did;
	}
}

function bluesky_probe_detect(array &$hookData)
{
	// Don't overwrite an existing result
	if (isset($hookData['result'])) {
		return;
	}

	// Avoid a lookup for the wrong network
	if (!in_array($hookData['network'], ['', Protocol::BLUESKY])) {
		return;
	}

	$pconfig = DBA::selectFirst('pconfig', ['uid'], ["`cat` = ? AND `k` = ? AND `v` != ?", 'bluesky', 'access_token', '']);
	if (empty($pconfig['uid'])) {
		return;
	}

	if (parse_url($hookData['uri'], PHP_URL_SCHEME) == 'did') {
		$did = $hookData['uri'];
	} elseif (preg_match('#^' . BLUESKY_HOST . '/profile/(.+)#', $hookData['uri'], $matches)) {
		$did = bluesky_get_did($pconfig['uid'], $matches[1]);
		if (empty($did)) {
			return;
		}
	} else {
		return;
	}

	$token = bluesky_get_token($pconfig['uid']);
	if (empty($token)) {
		return;
	}

	$data = bluesky_xrpc_get($pconfig['uid'], 'app.bsky.actor.getProfile', ['actor' => $did]);
	if (empty($data)) {
		return;
	}

	$hookData['result'] = bluesky_get_contact_fields($data, 0, false);

	// Preparing probe data. This differs slightly from the contact array
	$hookData['result']['about']    = HTML::toBBCode($data->description ?? '');
	$hookData['result']['photo']    = $data->avatar ?? '';
	$hookData['result']['header']   = $data->banner ?? '';
	$hookData['result']['batch']    = '';
	$hookData['result']['notify']   = '';
	$hookData['result']['poll']     = '';
	$hookData['result']['poco']     = '';
	$hookData['result']['pubkey']   = '';
	$hookData['result']['priority'] = 0;
	$hookData['result']['guid']     = '';
}

function bluesky_item_by_link(array &$hookData)
{
	// Don't overwrite an existing result
	if (isset($hookData['item_id'])) {
		return;
	}

	$token = bluesky_get_token($hookData['uid']);
	if (empty($token)) {
		return;
	}

	if (!preg_match('#^' . BLUESKY_HOST . '/profile/(.+)/post/(.+)#', $hookData['uri'], $matches)) {
		return;
	}

	$did = bluesky_get_did($hookData['uid'], $matches[1]);
	if (empty($did)) {
		return;
	}

	Logger::debug('Found bluesky post', ['url' => $hookData['uri'], 'handle' => $matches[1], 'did' => $did, 'cid' => $matches[2]]);

	$uri = 'at://' . $did . '/app.bsky.feed.post/' . $matches[2];

	$uri = bluesky_fetch_missing_post($uri, $hookData['uid'], 0, 0);
	Logger::debug('Got post', ['profile' => $matches[1], 'cid' => $matches[2], 'result' => $uri]);
	if (!empty($uri)) {
		$item = Post::selectFirst(['id'], ['uri' => $uri, 'uid' => $hookData['uid']]);
		if (!empty($item['id'])) {
			$hookData['item_id'] = $item['id'];
		}
	}
}

function bluesky_support_follow(array &$data)
{
	if ($data['protocol'] == Protocol::BLUESKY) {
		$data['result'] = true;
	}
}

function bluesky_support_probe(array &$data)
{
	if ($data['protocol'] == Protocol::BLUESKY) {
		$data['result'] = true;
	}
}

function bluesky_follow(array &$hook_data)
{
	$token = bluesky_get_token($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	Logger::debug('Check if contact is bluesky', ['data' => $hook_data]);
	$contact = DBA::selectFirst('contact', [], ['network' => Protocol::BLUESKY, 'url' => $hook_data['url'], 'uid' => [0, $hook_data['uid']]]);
	if (empty($contact)) {
		return;
	}

	$record = [
		'subject'   => $contact['url'],
		'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
		'$type'     => 'app.bsky.graph.follow'
	];

	$post = [
		'collection' => 'app.bsky.graph.follow',
		'repo'       => DI::pConfig()->get($hook_data['uid'], 'bluesky', 'did'),
		'record'     => $record
	];

	$activity = bluesky_xrpc_post($hook_data['uid'], 'com.atproto.repo.createRecord', $post);
	if (!empty($activity->uri)) {
		$hook_data['contact'] = $contact;
		Logger::debug('Successfully start following', ['url' => $contact['url'], 'uri' => $activity->uri]);
	}
}

function bluesky_unfollow(array &$hook_data)
{
	$token = bluesky_get_token($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	if ($hook_data['contact']['network'] != Protocol::BLUESKY) {
		return;
	}

	$data = bluesky_xrpc_get($hook_data['uid'], 'app.bsky.actor.getProfile', ['actor' => $hook_data['contact']['url']]);
	if (empty($data->viewer) || empty($data->viewer->following)) {
		return;
	}

	bluesky_delete_post($data->viewer->following, $hook_data['uid']);

	$hook_data['result'] = true;
}

function bluesky_block(array &$hook_data)
{
	$token = bluesky_get_token($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	Logger::debug('Check if contact is bluesky', ['data' => $hook_data]);
	$contact = DBA::selectFirst('contact', [], ['network' => Protocol::BLUESKY, 'url' => $hook_data['url'], 'uid' => [0, $hook_data['uid']]]);
	if (empty($contact)) {
		return;
	}

	$record = [
		'subject'   => $contact['url'],
		'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
		'$type'     => 'app.bsky.graph.block'
	];

	$post = [
		'collection' => 'app.bsky.graph.block',
		'repo'       => DI::pConfig()->get($hook_data['uid'], 'bluesky', 'did'),
		'record'     => $record
	];

	$activity = bluesky_xrpc_post($hook_data['uid'], 'com.atproto.repo.createRecord', $post);
	if (!empty($activity->uri)) {
		$cdata = Contact::getPublicAndUserContactID($hook_data['contact']['id'], $hook_data['uid']);
		if (!empty($cdata['user'])) {
			Contact::remove($cdata['user']);
		}
		Logger::debug('Successfully blocked contact', ['url' => $hook_data['contact']['url'], 'uri' => $activity->uri]);
	}
}

function bluesky_unblock(array &$hook_data)
{
	$token = bluesky_get_token($hook_data['uid']);
	if (empty($token)) {
		return;
	}

	if ($hook_data['contact']['network'] != Protocol::BLUESKY) {
		return;
	}

	$data = bluesky_xrpc_get($hook_data['uid'], 'app.bsky.actor.getProfile', ['actor' => $hook_data['contact']['url']]);
	if (empty($data->viewer) || empty($data->viewer->blocking)) {
		return;
	}

	bluesky_delete_post($data->viewer->blocking, $hook_data['uid']);

	$hook_data['result'] = true;
}

function bluesky_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled      = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post') ?? false;
	$def_enabled  = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default') ?? false;
	$host         = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'host') ?: 'https://bsky.social';
	$handle       = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'handle');
	$did          = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'did');
	$token        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'access_token');
	$import       = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'import') ?? false;
	$import_feeds = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'import_feeds') ?? false;

	$status = $token ? DI::l10n()->t("You are authenticated to Bluesky. For security reasons the password isn't stored.") : DI::l10n()->t('You are not authenticated. Please enter the app password.');

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/bluesky/');
	$html = Renderer::replaceMacros($t, [
		'$enable'       => ['bluesky', DI::l10n()->t('Enable Bluesky Post Addon'), $enabled],
		'$bydefault'    => ['bluesky_bydefault', DI::l10n()->t('Post to Bluesky by default'), $def_enabled],
		'$import'       => ['bluesky_import', DI::l10n()->t('Import the remote timeline'), $import],
		'$import_feeds' => ['bluesky_import_feeds', DI::l10n()->t('Import the pinned feeds'), $import_feeds, DI::l10n()->t('When activated, Posts will be imported from all the feeds that you pinned in Bluesky.')],
		'$host'         => ['bluesky_host', DI::l10n()->t('Bluesky host'), $host, '', '', 'readonly'],
		'$handle'       => ['bluesky_handle', DI::l10n()->t('Bluesky handle'), $handle],
		'$did'          => ['bluesky_did', DI::l10n()->t('Bluesky DID'), $did, DI::l10n()->t('This is the unique identifier. It will be fetched automatically, when the handle is entered.'), '', 'readonly'],
		'$password'     => ['bluesky_password', DI::l10n()->t('Bluesky app password'), '', DI::l10n()->t("Please don't add your real password here, but instead create a specific app password in the Bluesky settings.")],
		'$status'       => $status
	]);

	$data = [
		'connector' => 'bluesky',
		'title'     => DI::l10n()->t('Bluesky Import/Export'),
		'image'     => 'images/bluesky.jpg',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}

function bluesky_settings_post(array &$b)
{
	if (empty($_POST['bluesky-submit'])) {
		return;
	}

	$old_host   = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'host');
	$old_handle = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'handle');
	$old_did    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'did');

	$host   = $_POST['bluesky_host'];
	$handle = $_POST['bluesky_handle'];

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'post',            intval($_POST['bluesky']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default', intval($_POST['bluesky_bydefault']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'host',            $host);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'handle',          $handle);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'import',          intval($_POST['bluesky_import']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'import_feeds',    intval($_POST['bluesky_import_feeds']));

	if (!empty($host) && !empty($handle)) {
		if (empty($old_did) || $old_host != $host || $old_handle != $handle) {
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'did', bluesky_get_did(DI::userSession()->getLocalUserId(), DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'handle')));
		}
	} else {
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'did');
	}

	if (!empty($_POST['bluesky_password'])) {
		bluesky_create_token(DI::userSession()->getLocalUserId(), $_POST['bluesky_password']);
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
				DI::l10n()->t('Post to Bluesky'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default')
			]
		];
	}
}

function bluesky_cron()
{
	$last = DI::keyValue()->get('bluesky_last_poll');

	$poll_interval = intval(DI::config()->get('bluesky', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = BLUESKY_DEFAULT_POLL_INTERVAL;
	}

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			Logger::notice('poll interval not reached');
			return;
		}
	}
	Logger::notice('cron_start');

	$abandon_days = intval(DI::config()->get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'bluesky', 'k' => 'import', 'v' => true]);
	foreach ($pconfigs as $pconfig) {
		if ($abandon_days != 0) {
			if (!DBA::exists('user', ["`uid` = ? AND `login_date` >= ?", $pconfig['uid'], $abandon_limit])) {
				Logger::notice('abandoned account: timeline from user will not be imported', ['user' => $pconfig['uid']]);
				continue;
			}
		}

		// Refresh the token now, so that it doesn't need to be refreshed in parallel by the following workers
		bluesky_get_token($pconfig['uid']);

		Worker::add(['priority' => Worker::PRIORITY_MEDIUM, 'force_priority' => true], 'addon/bluesky/bluesky_timeline.php', $pconfig['uid']);
		Worker::add(['priority' => Worker::PRIORITY_MEDIUM, 'force_priority' => true], 'addon/bluesky/bluesky_notifications.php', $pconfig['uid']);

		if (DI::pConfig()->get($pconfig['uid'], 'bluesky', 'import_feeds')) {
			$feeds = bluesky_get_feeds($pconfig['uid']);
			foreach ($feeds as $feed) {
				Worker::add(['priority' => Worker::PRIORITY_MEDIUM, 'force_priority' => true], 'addon/bluesky/bluesky_feed.php', $pconfig['uid'], $feed);
			}
		}
	}

	$last_clean = DI::keyValue()->get('bluesky_last_clean');
	if (empty($last_clean) || ($last_clean + 86400 < time())) {
		Logger::notice('Start contact cleanup');
		$contacts = DBA::select('account-user-view', ['id', 'pid'], ["`network` = ? AND `uid` != ? AND `rel` = ?", Protocol::BLUESKY, 0, Contact::NOTHING]);
		while ($contact = DBA::fetch($contacts)) {
			Worker::add(Worker::PRIORITY_LOW, 'MergeContact', $contact['pid'], $contact['id'], 0);
		}
		DBA::close($contacts);
		DI::keyValue()->set('bluesky_last_clean', time());
		Logger::notice('Contact cleanup done');
	}

	Logger::notice('cron_end');

	DI::keyValue()->set('bluesky_last_poll', time());
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
		// Don't post if it isn't a reply to a bluesky post
		if (($post['parent'] != $post['id']) && !Post::exists(['id' => $post['parent'], 'network' => Protocol::BLUESKY])) {
			Logger::notice('No bluesky parent found', ['item' => $post['id']]);
			$b['execute'] = false;
			return;
		}
	} elseif (!strstr($post['postopts'] ?? '', 'bluesky') || ($post['parent'] != $post['id']) || $post['private']) {
		DI::logger()->info('Activities are never exported when we don\'t import the bluesky timeline', ['uid' => $post['uid']]);
		$b['execute'] = false;
		return;
	}
}

function bluesky_post_local(array &$b)
{
	if ($b['edit']) {
		return;
	}

	if (!DI::userSession()->getLocalUserId() || (DI::userSession()->getLocalUserId() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
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
	if (($b['created'] !== $b['edited']) && !$b['deleted']) {
		return;
	}

	if ($b['gravity'] != Item::GRAVITY_PARENT) {
		Logger::debug('Got comment', ['item' => $b]);

		if ($b['deleted']) {
			$uri = bluesky_get_uri_class($b['uri']);
			if (empty($uri)) {
				Logger::debug('Not a bluesky post', ['uri' => $b['uri']]);
				return;
			}
			bluesky_delete_post($b['uri'], $b['uid']);
			return;
		}

		$root   = bluesky_get_uri_class($b['parent-uri']);
		$parent = bluesky_get_uri_class($b['thr-parent']);

		if (empty($root) || empty($parent)) {
			Logger::debug('No bluesky post', ['parent' => $b['parent'], 'thr-parent' => $b['thr-parent']]);
			return;
		}

		if ($b['gravity'] == Item::GRAVITY_COMMENT) {
			Logger::debug('Posting comment', ['root' => $root, 'parent' => $parent]);
			bluesky_create_post($b, $root, $parent);
			return;
		} elseif (in_array($b['verb'], [Activity::LIKE, Activity::ANNOUNCE])) {
			bluesky_create_activity($b, $parent);
		}
		return;
	} elseif ($b['private'] || !strstr($b['postopts'], 'bluesky')) {
		return;
	}

	bluesky_create_post($b);
}

function bluesky_create_activity(array $item, stdClass $parent = null)
{
	$uid = $item['uid'];
	$token = bluesky_get_token($uid);
	if (empty($token)) {
		return;
	}

	$did  = DI::pConfig()->get($uid, 'bluesky', 'did');

	if ($item['verb'] == Activity::LIKE) {
		$record = [
			'subject'   => $parent,
			'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
			'$type'     => 'app.bsky.feed.like'
		];

		$post = [
			'collection' => 'app.bsky.feed.like',
			'repo'       => $did,
			'record'     => $record
		];
	} elseif ($item['verb'] == Activity::ANNOUNCE) {
		$record = [
			'subject'   => $parent,
			'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
			'$type'     => 'app.bsky.feed.repost'
		];

		$post = [
			'collection' => 'app.bsky.feed.repost',
			'repo'       => $did,
			'record'     => $record
		];
	}

	$activity = bluesky_xrpc_post($uid, 'com.atproto.repo.createRecord', $post);
	if (empty($activity)) {
		return;
	}
	Logger::debug('Activity done', ['return' => $activity]);
	$uri = bluesky_get_uri($activity);
	Item::update(['extid' => $uri], ['id' => $item['id']]);
	Logger::debug('Set extid', ['id' => $item['id'], 'extid' => $activity]);
}

function bluesky_create_post(array $item, stdClass $root = null, stdClass $parent = null)
{
	$uid = $item['uid'];
	$token = bluesky_get_token($uid);
	if (empty($token)) {
		return;
	}

	$did  = DI::pConfig()->get($uid, 'bluesky', 'did');
	$urls = bluesky_get_urls(Post\Media::removeFromBody($item['body']));
	$item['body'] = $urls['body'];

	$msg = Plaintext::getPost($item, 300, false, BBCode::BLUESKY);
	foreach ($msg['parts'] as $key => $part) {

		$facets = bluesky_get_facets($part, $urls['urls']);

		$record = [
			'text'      => $facets['body'],
			'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
			'$type'     => 'app.bsky.feed.post'
		];

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

		$post = [
			'collection' => 'app.bsky.feed.post',
			'repo'       => $did,
			'record'     => $record
		];

		$parent = bluesky_xrpc_post($uid, 'com.atproto.repo.createRecord', $post);
		if (empty($parent)) {
			if ($part == 0) {
				Worker::defer();
			}
			return;
		}
		Logger::debug('Posting done', ['return' => $parent]);
		if (empty($root)) {
			$root = $parent;
		}
		if (($key == 0) && ($item['gravity'] != Item::GRAVITY_PARENT)) {
			$uri = bluesky_get_uri($parent);
			Item::update(['extid' => $uri], ['id' => $item['id']]);
			Logger::debug('Set extid', ['id' => $item['id'], 'extid' => $uri]);
		}
	}
}

function bluesky_get_urls(string $body): array
{
	// Remove all hashtag and mention links
	$body = preg_replace("/([#@!])\[url\=(.*?)\](.*?)\[\/url\]/ism", '$1$3', $body);

	$body = BBCode::expandVideoLinks($body);
	$urls = [];

	// Search for pure links
	if (preg_match_all("/\[url\](https?:.*?)\[\/url\]/ism", $body, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$text = Strings::getStyledURL($match[1]);
			$hash = bluesky_get_hash_for_url($match[0], mb_strlen($text));
			$urls[] = ['url' => $match[1], 'text' => $text, 'hash' => $hash];
			$body = str_replace($match[0], $hash, $body);
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
				$hash = bluesky_get_hash_for_url($match[0], mb_strlen($text));
				$urls[] = ['url' => $match[1], 'text' => $text, 'hash' => $hash];
				$body = str_replace($match[0], $hash, $body);
			} else {
				$text = Strings::getStyledURL($match[1]);
				$hash = bluesky_get_hash_for_url($match[0], mb_strlen($text));
				$urls[] = ['url' => $match[1], 'text' => $text, 'hash' => $hash];
				$body = str_replace($match[0], $text . ' ' . $hash, $body);
			}
		}
	}

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

		$facet = new stdClass;
		$facet->index = new stdClass;
		$facet->index->byteEnd   = $pos + strlen($url['text']);
		$facet->index->byteStart = $pos;

		$feature = new stdClass;
		$feature->uri = $url['url'];
		$type = '$type';
		$feature->$type = 'app.bsky.richtext.facet#link';

		$facet->features = [$feature];
		$facets[] = $facet;
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
			$blob = bluesky_upload_blob($uid, $photo);
			if (empty($blob)) {
				return [];
			}
			$images[] = ['alt' => $image['description'] ?? '', 'image' => $blob];
		}
		if (!empty($images)) {
			$record['embed'] = ['$type' => 'app.bsky.embed.images', 'images' => $images];
		}
	} elseif ($msg['type'] == 'link') {
		$record['embed'] = [
			'$type'    => 'app.bsky.embed.external',
			'external' => [
				'uri'         => $msg['url'],
				'title'       => $msg['title'] ?? '',
				'description' => $msg['description'] ?? '',
			]
		];
		if (!empty($msg['image'])) {
			$photo = Photo::createPhotoForExternalResource($msg['image']);
			$blob = bluesky_upload_blob($uid, $photo);
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

	$picture = new Image($content, $photo['type']);
	$height  = $picture->getHeight();
	$width   = $picture->getWidth();
	$size    = strlen($content);

	$picture    = Photo::resizeToFileSize($picture, BLUESKY_IMAGE_SIZE[$retrial]);
	$new_height = $picture->getHeight();
	$new_width  = $picture->getWidth();
	$content    = $picture->asString();
	$new_size   = strlen($content);

	Logger::info('Uploading', ['uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size]);

	$data = bluesky_post($uid, '/xrpc/com.atproto.repo.uploadBlob', $content, ['Content-type' => $photo['type'], 'Authorization' => ['Bearer ' . bluesky_get_token($uid)]]);
	if (empty($data)) {
		Logger::info('Uploading failed', ['uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size]);
		return null;
	}

	Logger::debug('Uploaded blob', ['return' => $data, 'uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size]);
	return $data->blob;
}

function bluesky_delete_post(string $uri, int $uid)
{
	$parts = bluesky_get_uri_parts($uri);
	if (empty($parts)) {
		Logger::debug('No uri delected', ['uri' => $uri]);
		return;
	}
	bluesky_xrpc_post($uid, 'com.atproto.repo.deleteRecord', $parts);
	Logger::debug('Deleted', ['parts' => $parts]);
}

function bluesky_fetch_timeline(int $uid)
{
	$data = bluesky_xrpc_get($uid, 'app.bsky.feed.getTimeline');
	if (empty($data)) {
		return;
	}

	if (empty($data->feed)) {
		return;
	}

	foreach (array_reverse($data->feed) as $entry) {
		bluesky_process_post($entry->post, $uid, Item::PR_NONE, 0);
		if (!empty($entry->reason)) {
			bluesky_process_reason($entry->reason, bluesky_get_uri($entry->post), $uid);
		}
	}

	// @todo Support paging
	// [cursor] => 1684670516000::bafyreidq3ilwslmlx72jf5vrk367xcc63s6lrhzlyup2bi3zwcvso6w2vi
}

function bluesky_process_reason(stdClass $reason, string $uri, int $uid)
{
	$type = '$type';
	if ($reason->$type != 'app.bsky.feed.defs#reasonRepost') {
		return;
	}

	$contact = bluesky_get_contact($reason->by, $uid, $uid);

	$item = [
		'network'       => Protocol::BLUESKY,
		'uid'           => $uid,
		'wall'          => false,
		'uri'           => $reason->by->did . '/app.bsky.feed.repost/' . $reason->indexedAt,
		'private'       => Item::UNLISTED,
		'verb'          => Activity::POST,
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

	$item['guid']         = Item::guidFromUri($item['uri'], $contact['alias']);
	$item['owner-name']   = $item['author-name'];
	$item['owner-link']   = $item['author-link'];
	$item['owner-avatar'] = $item['author-avatar'];
	if (Item::insert($item)) {
		$cdata = Contact::getPublicAndUserContactID($contact['id'], $uid);
		Item::update(['post-reason' => Item::PR_ANNOUNCEMENT, 'causer-id' => $cdata['public']], ['uri' => $uri, 'uid' => $uid]);
	}
}

function bluesky_fetch_notifications(int $uid)
{
	$data = bluesky_xrpc_get($uid, 'app.bsky.notification.listNotifications');
	if (empty($data->notifications)) {
		return;
	}
	foreach ($data->notifications as $notification) {
		$uri = bluesky_get_uri($notification);
		if (Post::exists(['uri' => $uri, 'uid' => $uid]) || Post::exists(['extid' => $uri, 'uid' => $uid])) {
			Logger::debug('Notification already processed', ['uid' => $uid, 'reason' => $notification->reason, 'uri' => $uri, 'indexedAt' => $notification->indexedAt]);
			continue;
		}
		Logger::debug('Process notification', ['uid' => $uid, 'reason' => $notification->reason, 'uri' => $uri, 'indexedAt' => $notification->indexedAt]);
		switch ($notification->reason) {
			case 'like':
				$item = bluesky_get_header($notification, $uri, $uid, $uid);
				$item['gravity'] = Item::GRAVITY_ACTIVITY;
				$item['body'] = $item['verb'] = Activity::LIKE;
				$item['thr-parent'] = bluesky_get_uri($notification->record->subject);
				$item['thr-parent'] = bluesky_fetch_missing_post($item['thr-parent'], $uid, $item['contact-id'], 0);
				if (!empty($item['thr-parent'])) {
					$data = Item::insert($item);
					Logger::debug('Got like', ['uid' => $uid, 'result' => $data, 'uri' => $uri]);
				} else {
					Logger::info('Thread parent not found', ['uid' => $uid, 'parent' => $item['thr-parent'], 'uri' => $uri]);
				}
			break;

			case 'repost':
				$item = bluesky_get_header($notification, $uri, $uid, $uid);
				$item['gravity'] = Item::GRAVITY_ACTIVITY;
				$item['body'] = $item['verb'] = Activity::ANNOUNCE;
				$item['thr-parent'] = bluesky_get_uri($notification->record->subject);
				$item['thr-parent'] = bluesky_fetch_missing_post($item['thr-parent'], $uid, $item['contact-id'], 0);
				if (!empty($item['thr-parent'])) {
					$data = Item::insert($item);
					Logger::debug('Got repost', ['uid' => $uid, 'result' => $data, 'uri' => $uri]);
				} else {
					Logger::info('Thread parent not found', ['uid' => $uid, 'parent' => $item['thr-parent'], 'uri' => $uri]);
				}
			break;

			case 'follow':
				$contact = bluesky_get_contact($notification->author, $uid, $uid);
				Logger::debug('New follower', ['uid' => $uid, 'nick' => $contact['nick'], 'uri' => $uri]);
				break;

			case 'mention':
				$data = bluesky_process_post($notification, $uid, Item::PR_PUSHED, 0);
				Logger::debug('Got mention', ['uid' => $uid, 'result' => $data, 'uri' => $uri]);
				break;

			case 'reply':
				$data = bluesky_process_post($notification, $uid, Item::PR_PUSHED, 0);
				Logger::debug('Got reply', ['uid' => $uid, 'result' => $data, 'uri' => $uri]);
				break;

			case 'quote':
				$data = bluesky_process_post($notification, $uid, Item::PR_PUSHED, 0);
				Logger::debug('Got quote', ['uid' => $uid, 'result' => $data, 'uri' => $uri]);
				break;

			default:
				Logger::notice('Unhandled reason', ['reason' => $notification->reason, 'uri' => $uri]);
				break;
		}
	}
}

function bluesky_fetch_feed(int $uid, string $feed)
{
	$data = bluesky_xrpc_get($uid, 'app.bsky.feed.getFeed', ['feed' => $feed]);
	if (empty($data)) {
		return;
	}

	if (empty($data->feed)) {
		return;
	}

	$feeddata = bluesky_xrpc_get($uid, 'app.bsky.feed.getFeedGenerator', ['feed' => $feed]);
	if (!empty($feeddata)) {
		$feedurl  = $feeddata->view->uri;
		$feedname = $feeddata->view->displayName;
	} else {
		$feedurl  = $feed;
		$feedname = $feed;
	}

	foreach (array_reverse($data->feed) as $entry) {
		if (!Relay::isWantedLanguage($entry->post->record->text)) {
			Logger::debug('Unwanted language detected', ['text' => $entry->post->record->text]);
			continue;
		}
		$id = bluesky_process_post($entry->post, $uid, Item::PR_TAG, 0);
		if (!empty($id)) {
			$post = Post::selectFirst(['uri-id'], ['id' => $id]);
			if (!empty($post['uri-id'])) {
				$stored = Post\Category::storeFileByURIId($post['uri-id'], $uid, Post\Category::SUBCRIPTION, $feedname, $feedurl);
				Logger::debug('Stored tag subscription for user', ['uri-id' => $post['uri-id'], 'uid' => $uid, 'name' => $feedname, 'url' => $feedurl, 'stored' => $stored]);
			} else {
				Logger::notice('Post not found', ['id' => $id, 'entry' => $entry]);
			}
		}
		if (!empty($entry->reason)) {
			bluesky_process_reason($entry->reason, bluesky_get_uri($entry->post), $uid);
		}
	}
}

function bluesky_process_post(stdClass $post, int $uid, int $post_reason, $level): int
{
	$uri = bluesky_get_uri($post);

	if ($id = Post::selectFirst(['id'], ['uri' => $uri, 'uid' => $uid])) {
		return $id['id'];	
	}

	if ($id = Post::selectFirst(['id'], ['extid' => $uri, 'uid' => $uid])) {
		return $id['id'];	
	}

	Logger::debug('Importing post', ['uid' => $uid, 'indexedAt' => $post->indexedAt, 'uri' => $post->uri, 'cid' => $post->cid, 'root' => $post->record->reply->root ?? '']);

	$item = bluesky_get_header($post, $uri, $uid, $uid);
	$item = bluesky_get_content($item, $post->record, $uri, $uid, $level);
	if (empty($item)) {
		return 0;
	}

	if (!empty($post->embed)) {
		$item = bluesky_add_media($post->embed, $item, $uid, $level);
	}

	if (empty($item['post-reason'])) {
		$item['post-reason'] = $post_reason;
	}

	return item::insert($item);
}

function bluesky_get_header(stdClass $post, string $uri, int $uid, int $fetch_uid): array
{
	$parts = bluesky_get_uri_parts($uri);
	if (empty($post->author)) {
		return [];
	}
	$contact = bluesky_get_contact($post->author, $uid, $fetch_uid);
	$item = [
		'network'       => Protocol::BLUESKY,
		'uid'           => $uid,
		'wall'          => false,
		'uri'           => $uri,
		'guid'          => $post->cid,
		'private'       => Item::UNLISTED,
		'verb'          => Activity::POST,
		'contact-id'    => $contact['id'],
		'author-name'   => $contact['name'],
		'author-link'   => $contact['url'],
		'author-avatar' => $contact['avatar'],
		'plink'         => $contact['alias'] . '/post/' . $parts->rkey,
		'source'        => json_encode($post),
	];

	$item['uri-id']       = ItemURI::getIdByURI($uri);
	$item['owner-name']   = $item['author-name'];
	$item['owner-link']   = $item['author-link'];
	$item['owner-avatar'] = $item['author-avatar'];

	if (in_array($contact['rel'], [Contact::SHARING, Contact::FRIEND])) {
		$item['post-reason'] = Item::PR_FOLLOWER;
	}

	return $item;
}

function bluesky_get_content(array $item, stdClass $record, string $uri, int $uid, int $level): array
{
	if (empty($item)) {
		return [];
	}

	if (!empty($record->reply)) {
		$item['parent-uri'] = bluesky_get_uri($record->reply->root);
		if ($item['parent-uri'] != $uri) {
			$item['parent-uri'] = bluesky_fetch_missing_post($item['parent-uri'], $uid, $item['contact-id'], $level);
			if (empty($item['parent-uri'])) {
				return [];
			}
		}

		$item['thr-parent'] = bluesky_get_uri($record->reply->parent);
		if (!in_array($item['thr-parent'], [$uri, $item['parent-uri']])) {
			$item['thr-parent'] = bluesky_fetch_missing_post($item['thr-parent'], $uid, $item['contact-id'], $level, $item['parent-uri']);
			if (empty($item['thr-parent'])) {
				return [];
			}
		}
	}

	$item['body']    = bluesky_get_text($record);
	$item['created'] = DateTimeFormat::utc($record->createdAt, DateTimeFormat::MYSQL);
	return $item;
}

function bluesky_get_text(stdClass $record): string
{
	$text = $record->text ?? '';

	if (empty($record->facets)) {
		return $text;
	}

	$facets = [];
	foreach ($record->facets as $facet) {
		$facets[$facet->index->byteStart] = $facet;
	}
	krsort($facets);

	foreach ($facets as $facet) {
		$prefix   = substr($text, 0, $facet->index->byteStart);
		$linktext = substr($text, $facet->index->byteStart, $facet->index->byteEnd - $facet->index->byteStart);
		$suffix   = substr($text, $facet->index->byteEnd);

		$url  = '';
		$type = '$type';
		foreach ($facet->features as $feature) {

			switch ($feature->$type) {
				case 'app.bsky.richtext.facet#link':
					$url = $feature->uri;
					break;

				case 'app.bsky.richtext.facet#mention':
					$contact = Contact::getByURL($feature->did, null, ['id']);
					if (!empty($contact['id'])) {
						$url = DI::baseUrl() . '/contact/' . $contact['id'];
						if (substr($linktext, 0, 1) == '@') {
							$prefix .= '@';
							$linktext = substr($linktext, 1);
						}
					}
					break;

				default:
					Logger::notice('Unhandled feature type', ['type' => $feature->$type, 'record' => $record]);
					break;
			}
		}
		if (!empty($url)) {
			$text = $prefix . '[url=' . $url . ']' . $linktext . '[/url]' . $suffix;
		}
	}
	return $text;
}

function bluesky_add_media(stdClass $embed, array $item, int $fetch_uid, int $level): array
{
	$type = '$type';
	switch ($embed->$type) {
		case 'app.bsky.embed.images#view':
			foreach ($embed->images as $image) {
				$media = [
					'uri-id'      => $item['uri-id'],
					'type'        => Post\Media::IMAGE,
					'url'         => $image->fullsize,
					'preview'     => $image->thumb,
					'description' => $image->alt,
				];
				Post\Media::insert($media);
			}
			break;

		case 'app.bsky.embed.external#view':
			$media = [
				'uri-id' => $item['uri-id'],
				'type'        => Post\Media::HTML,
				'url'         => $embed->external->uri,
				'name'        => $embed->external->title,
				'description' => $embed->external->description,
			];
			Post\Media::insert($media);
			break;

		case 'app.bsky.embed.record#view':
			$uri = bluesky_get_uri($embed->record);
			$shared = Post::selectFirst(['uri-id'], ['uri' => $uri, 'uid' => $item['uid']]);
			if (empty($shared)) {
				if (empty($embed->record->value)) {
					Logger::info('Record has got no value', ['record' => $embed->record]);
					break;
				}
				$shared = bluesky_get_header($embed->record, $uri, 0, $fetch_uid);
				$shared = bluesky_get_content($shared, $embed->record->value, $uri, $item['uid'], $level);
				if (!empty($shared)) {
					if (!empty($embed->record->embeds)) {
						foreach ($embed->record->embeds as $single) {
							$shared = bluesky_add_media($single, $shared, $fetch_uid, $level);
						}
					}
					Item::insert($shared);
				}
			}
			if (!empty($shared['uri-id'])) {
				$item['quote-uri-id'] = $shared['uri-id'];
			}
			break;

		case 'app.bsky.embed.recordWithMedia#view':
			$uri = bluesky_get_uri($embed->record->record);
			$shared = Post::selectFirst(['uri-id'], ['uri' => $uri, 'uid' => $item['uid']]);
			if (empty($shared)) {
				$shared = bluesky_get_header($embed->record->record, $uri, 0, $fetch_uid);
				$shared = bluesky_get_content($shared, $embed->record->record->value, $uri, $item['uid'], $level);
				if (!empty($shared)) {
					if (!empty($embed->record->record->embeds)) {
						foreach ($embed->record->record->embeds as $single) {
							$shared = bluesky_add_media($single, $shared, $fetch_uid, $level);
						}
					}
					Item::insert($shared);
				}
			}
			if (!empty($shared['uri-id'])) {
				$item['quote-uri-id'] = $shared['uri-id'];
			}

			if (!empty($embed->media)) {
				$item = bluesky_add_media($embed->media, $item, $fetch_uid, $level);
			}
			break;

		default:
			Logger::notice('Unhandled embed type', ['type' => $embed->$type, 'embed' => $embed]);
			break;
	}
	return $item;
}

function bluesky_get_uri(stdClass $post): string
{
	if (empty($post->cid)) {
		Logger::info('Invalid URI', ['post' => $post, 'callstack' => System::callstack(10, 0, true)]);
		return '';
	}
	return $post->uri . ':' . $post->cid;
}

function bluesky_get_uri_class(string $uri): ?stdClass
{
	if (empty($uri)) {
		return null;
	}

	$elements = explode(':', $uri);
	if (empty($elements) || ($elements[0] != 'at')) {
		$post = Post::selectFirstPost(['extid'], ['uri' => $uri]);
		return bluesky_get_uri_class($post['extid'] ?? '');
	}

	$class = new stdClass;

	$class->cid = array_pop($elements);
	$class->uri = implode(':', $elements);

	if ((substr_count($class->uri, '/') == 2) && (substr_count($class->cid, '/') == 2)) {
		$class->uri .= ':' . $class->cid;
		$class->cid = '';
	}

	return $class;
}

function bluesky_get_uri_parts(string $uri): ?stdClass
{
	$class = bluesky_get_uri_class($uri);
	if (empty($class)) {
		return null;
	}

	$parts = explode('/', substr($class->uri, 5));

	$class = new stdClass;

	$class->repo       = $parts[0];
	$class->collection = $parts[1];
	$class->rkey       = $parts[2];

	return $class;
}

function bluesky_fetch_missing_post(string $uri, int $uid, int $causer, int $level, string $fallback = ''): string
{
	$fetched_uri = bluesky_fetch_post($uri, $uid);
	if (!empty($fetched_uri)) {
		return $fetched_uri;
	}

	if (++$level > 100) {
		Logger::info('Recursion level too deep', ['level' => $level, 'uid' => $uid, 'uri' => $uri, 'fallback' => $fallback]);
		// When the level is too deep we will fallback to the parent uri.
		// Allthough the threading won't be correct, we at least had stored all posts and won't try again
		return $fallback;
	}

	$class = bluesky_get_uri_class($uri);
	$fetch_uri = $class->uri;

	Logger::debug('Fetch missing post', ['level' => $level, 'uid' => $uid, 'uri' => $uri]);
	$data = bluesky_xrpc_get($uid, 'app.bsky.feed.getPostThread', ['uri' => $fetch_uri]);
	if (empty($data)) {
		Logger::info('Thread was not fetched', ['level' => $level, 'uid' => $uid, 'uri' => $uri, 'fallback' => $fallback]);
		return $fallback;
	}

	Logger::debug('Reply count', ['replies' => $data->thread->post->replyCount, 'level' => $level, 'uid' => $uid, 'uri' => $uri]);

	if ($causer != 0) {
		$cdata = Contact::getPublicAndUserContactID($causer, $uid);
	} else {
		$cdata = [];
	}

	return bluesky_process_thread($data->thread, $uid, $cdata, $level);
}

function bluesky_fetch_post(string $uri, int $uid): string
{
	if (Post::exists(['uri' => $uri, 'uid' => [$uid, 0]])) {
		Logger::debug('Post exists', ['uri' => $uri]);
		return $uri;
	}

	$reply = Post::selectFirst(['uri'], ['extid' => $uri, 'uid' => [$uid, 0]]);
	if (!empty($reply['uri'])) {
		Logger::debug('Post with extid exists', ['uri' => $uri]);
		return $reply['uri'];
	}
	return '';
}

function bluesky_process_thread(stdClass $thread, int $uid, array $cdata, int $level): string
{
	if (empty($thread->post)) {
		Logger::info('Invalid post', ['post' => $thread, 'callstack' => System::callstack(10, 0, true)]);
		return '';
	}
	$uri = bluesky_get_uri($thread->post);

	$fetched_uri = bluesky_fetch_post($uri, $uid);
	if (empty($fetched_uri)) {
		Logger::debug('Process missing post', ['uri' => $uri]);
		$item = bluesky_get_header($thread->post, $uri, $uid, $uid);
		$item = bluesky_get_content($item, $thread->post->record, $uri, $uid, $level);
		if (!empty($item)) {
			$item['post-reason'] = Item::PR_FETCHED;

			if (!empty($cdata['public'])) {
				$item['causer-id']   = $cdata['public'];
			}

			if (!empty($thread->post->embed)) {
				$item = bluesky_add_media($thread->post->embed, $item, $uid, $level);
			}
			$id = Item::insert($item);
			if (!$id) {
				Logger::info('Item has not not been stored', ['uri' => $uri]);
				return '';
			}
			Logger::debug('Stored item', ['id' => $id, 'uri' => $uri]);
		} else {
			Logger::info('Post has not not been fetched', ['uri' => $uri]);
			return '';
		}
	} else {
		Logger::debug('Post exists', ['uri' => $uri]);
		$uri = $fetched_uri;
	}

	foreach ($thread->replies ?? [] as $reply) {
		$reply_uri = bluesky_process_thread($reply, $uid, $cdata, $level);
		Logger::debug('Reply has been processed', ['uri' => $uri, 'reply' => $reply_uri]);
	}

	return $uri;
}

function bluesky_get_contact(stdClass $author, int $uid, int $fetch_uid): array
{
	$condition = ['network' => Protocol::BLUESKY, 'uid' => 0, 'url' => $author->did];
	$contact = Contact::selectFirst(['id', 'updated'], $condition);

	$update = empty($contact) || $contact['updated'] < DateTimeFormat::utc('now -24 hours');

	$public_fields = $fields = bluesky_get_contact_fields($author, $fetch_uid, $update);

	$public_fields['uid'] = 0;
	$public_fields['rel'] = Contact::NOTHING;

	if (empty($contact)) {
		$cid = Contact::insert($public_fields);
	} else {
		$cid = $contact['id'];
		Contact::update($public_fields, ['id' => $cid], true);
	}

	if ($uid != 0) {
		$condition = ['network' => Protocol::BLUESKY, 'uid' => $uid, 'url' => $author->did];

		$contact = Contact::selectFirst(['id', 'rel', 'uid'], $condition);
		if (!isset($fields['rel']) && isset($contact['rel'])) {
			$fields['rel'] = $contact['rel'];
		} elseif (!isset($fields['rel'])) {
			$fields['rel'] = Contact::NOTHING;
		}
	}

	if (($uid != 0) && ($fields['rel'] != Contact::NOTHING)) {
		if (empty($contact)) {
			$cid = Contact::insert($fields);
		} else {
			$cid = $contact['id'];
			Contact::update($fields, ['id' => $cid], true);
		}
		Logger::debug('Get user contact', ['id' => $cid, 'uid' => $uid, 'update' => $update]);
	} else {
		Logger::debug('Get public contact', ['id' => $cid, 'uid' => $uid, 'update' => $update]);
	}
	if (!empty($author->avatar)) {
		Contact::updateAvatar($cid, $author->avatar);
	}

	return Contact::getById($cid);
}

function bluesky_get_contact_fields(stdClass $author, int $uid, bool $update): array
{
	$fields = [
		'uid'      => $uid,
		'network'  => Protocol::BLUESKY,
		'priority' => 1,
		'writable' => true,
		'blocked'  => false,
		'readonly' => false,
		'pending'  => false,
		'baseurl'  => BLUESKY_HOST,
		'url'      => $author->did,
		'nurl'     => $author->did,
		'alias'    => BLUESKY_HOST . '/profile/' . $author->handle,
		'name'     => $author->displayName ?? $author->handle,
		'nick'     => $author->handle,
		'addr'     => $author->handle,
	];

	if (!$update) {
		Logger::debug('Got contact fields', ['uid' => $uid, 'url' => $fields['url']]);
		return $fields;
	}

	$data = bluesky_xrpc_get($uid, 'app.bsky.actor.getProfile', ['actor' => $author->did]);
	if (empty($data)) {
		Logger::debug('Error fetching contact fields', ['uid' => $uid, 'url' => $fields['url']]);
		return $fields;
	}

	$fields['updated'] = DateTimeFormat::utcNow(DateTimeFormat::MYSQL);

	if (!empty($data->description)) {
		$fields['about'] = HTML::toBBCode($data->description);
	}

	if (!empty($data->banner)) {
		$fields['header'] = $data->banner;
	}

	if (!empty($data->viewer)) {
		if (!empty($data->viewer->following) && !empty($data->viewer->followedBy)) {
			$fields['rel'] = Contact::FRIEND;
		} elseif (!empty($data->viewer->following) && empty($data->viewer->followedBy)) {
			$fields['rel'] = Contact::SHARING;
		} elseif (empty($data->viewer->following) && !empty($data->viewer->followedBy)) {
			$fields['rel'] = Contact::FOLLOWER;
		} else {
			$fields['rel'] = Contact::NOTHING;
		}
	}

	Logger::debug('Got updated contact fields', ['uid' => $uid, 'url' => $fields['url']]);
	return $fields;
}

function bluesky_get_feeds(int $uid): array
{
	$type = '$type';
	$preferences = bluesky_get_preferences($uid);
	foreach ($preferences->preferences as $preference) {
		if ($preference->$type == 'app.bsky.actor.defs#savedFeedsPref') {
			return $preference->pinned ?? [];
		}
	}
	return [];
}

function bluesky_get_preferences(int $uid): stdClass
{
	$cachekey = 'bluesky:preferences:' . $uid;
	$data = DI::cache()->get($cachekey);
	if (!is_null($data)) {
		return $data;
	}

	$data = bluesky_xrpc_get($uid, 'app.bsky.actor.getPreferences');

	DI::cache()->set($cachekey, $data, Duration::HOUR);
	return $data;
}

function bluesky_get_did(int $uid, string $handle): string
{
	$data = bluesky_get($uid, '/xrpc/com.atproto.identity.resolveHandle?handle=' . urlencode($handle));
	if (empty($data)) {
		return '';
	}
	Logger::debug('Got DID', ['return' => $data]);
	return $data->did;
}

function bluesky_get_token(int $uid): string
{
	$token   = DI::pConfig()->get($uid, 'bluesky', 'access_token');
	$created = DI::pConfig()->get($uid, 'bluesky', 'token_created');
	if (empty($token)) {
		return '';
	}

	if ($created + 300 < time()) {
		return bluesky_refresh_token($uid);
	}
	return $token;
}

function bluesky_refresh_token(int $uid): string
{
	$token = DI::pConfig()->get($uid, 'bluesky', 'refresh_token');

	$data = bluesky_post($uid, '/xrpc/com.atproto.server.refreshSession', '', ['Authorization' => ['Bearer ' . $token]]);
	if (empty($data)) {
		return '';
	}

	Logger::debug('Refreshed token', ['return' => $data]);
	DI::pConfig()->set($uid, 'bluesky', 'access_token', $data->accessJwt);
	DI::pConfig()->set($uid, 'bluesky', 'refresh_token', $data->refreshJwt);
	DI::pConfig()->set($uid, 'bluesky', 'token_created', time());
	return $data->accessJwt;
}

function bluesky_create_token(int $uid, string $password): string
{
	$did = DI::pConfig()->get($uid, 'bluesky', 'did');

	$data = bluesky_post($uid, '/xrpc/com.atproto.server.createSession', json_encode(['identifier' => $did, 'password' => $password]), ['Content-type' => 'application/json']);
	if (empty($data)) {
		return '';
	}

	Logger::debug('Created token', ['return' => $data]);
	DI::pConfig()->set($uid, 'bluesky', 'access_token', $data->accessJwt);
	DI::pConfig()->set($uid, 'bluesky', 'refresh_token', $data->refreshJwt);
	DI::pConfig()->set($uid, 'bluesky', 'token_created', time());
	return $data->accessJwt;
}

function bluesky_xrpc_post(int $uid, string $url, $parameters): ?stdClass
{
	return bluesky_post($uid, '/xrpc/' . $url, json_encode($parameters),  ['Content-type' => 'application/json', 'Authorization' => ['Bearer ' . bluesky_get_token($uid)]]);
}

function bluesky_post(int $uid, string $url, string $params, array $headers): ?stdClass
{
	try {
		$curlResult = DI::httpClient()->post(DI::pConfig()->get($uid, 'bluesky', 'host') . $url, $params, $headers);
	} catch (\Exception $e) {
		Logger::notice('Exception on post', ['exception' => $e]);
		return null;
	}

	if (!$curlResult->isSuccess()) {
		Logger::notice('API Error', ['error' => json_decode($curlResult->getBody()) ?: $curlResult->getBody()]);
		return null;
	}

	return json_decode($curlResult->getBody());
}

function bluesky_xrpc_get(int $uid, string $url, array $parameters = []): ?stdClass
{
	if (!empty($parameters)) {
		$url .= '?' . http_build_query($parameters);
	}

	return bluesky_get($uid, '/xrpc/' . $url, HttpClientAccept::JSON, [HttpClientOptions::HEADERS => ['Authorization' => ['Bearer ' . bluesky_get_token($uid)]]]);
}

function bluesky_get(int $uid, string $url, string $accept_content = HttpClientAccept::DEFAULT, array $opts = []): ?stdClass
{
	try {
		$curlResult = DI::httpClient()->get(DI::pConfig()->get($uid, 'bluesky', 'host') . $url, $accept_content, $opts);
	} catch (\Exception $e) {
		Logger::notice('Exception on get', ['exception' => $e]);
		return null;
	}

	if (!$curlResult->isSuccess()) {
		Logger::notice('API Error', ['error' => json_decode($curlResult->getBody()) ?: $curlResult->getBody()]);
		return null;
	}

	return json_decode($curlResult->getBody());
}
