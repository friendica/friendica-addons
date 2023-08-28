<?php
/**
 * Name: Tumblr Post Connector
 * Description: Post to Tumblr
 * Version: 2.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

use Friendica\Content\PageInfo;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\HTML;
use Friendica\Content\Text\NPF;
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
use Friendica\Model\Photo;
use Friendica\Model\Post;
use Friendica\Model\Tag;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Client\HttpClientAccept;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;
use Friendica\Protocol\Activity;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;
use Friendica\Util\Strings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

define('TUMBLR_DEFAULT_POLL_INTERVAL', 10); // given in minutes
define('TUMBLR_DEFAULT_MAXIMUM_TAGS', 10);

function tumblr_install()
{
	Hook::register('load_config',             __FILE__, 'tumblr_load_config');
	Hook::register('hook_fork',               __FILE__, 'tumblr_hook_fork');
	Hook::register('post_local',              __FILE__, 'tumblr_post_local');
	Hook::register('notifier_normal',         __FILE__, 'tumblr_send');
	Hook::register('jot_networks',            __FILE__, 'tumblr_jot_nets');
	Hook::register('connector_settings',      __FILE__, 'tumblr_settings');
	Hook::register('connector_settings_post', __FILE__, 'tumblr_settings_post');
	Hook::register('cron',                    __FILE__, 'tumblr_cron');
	Hook::register('support_follow',          __FILE__, 'tumblr_support_follow');
	Hook::register('support_probe',           __FILE__, 'tumblr_support_probe');
	Hook::register('follow',                  __FILE__, 'tumblr_follow');
	Hook::register('unfollow',                __FILE__, 'tumblr_unfollow');
	Hook::register('block',                   __FILE__, 'tumblr_block');
	Hook::register('unblock',                 __FILE__, 'tumblr_unblock');
	Hook::register('check_item_notification', __FILE__, 'tumblr_check_item_notification');
	Hook::register('probe_detect',            __FILE__, 'tumblr_probe_detect');
	Hook::register('item_by_link',            __FILE__, 'tumblr_item_by_link');
	Logger::info('installed tumblr');
}

function tumblr_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('tumblr'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function tumblr_check_item_notification(array &$notification_data)
{
	if (!tumblr_enabled_for_user($notification_data['uid'])) {
		return;
	}

	$page = tumblr_get_page($notification_data['uid']);
	if (empty($page)) {
		return;
	}

	$own_user = Contact::selectFirst(['url', 'alias'], ['network' => Protocol::TUMBLR, 'uid' => [0, $notification_data['uid']], 'poll' => 'tumblr::' . $page]);
	if ($own_user) {
		$notification_data['profiles'][] = $own_user['url'];
		$notification_data['profiles'][] = $own_user['alias'];
	}
}

function tumblr_probe_detect(array &$hookData)
{
	// Don't overwrite an existing result
	if (isset($hookData['result'])) {
		return;
	}

	// Avoid a lookup for the wrong network
	if (!in_array($hookData['network'], ['', Protocol::TUMBLR])) {
		return;
	}

	$hookData['result'] = tumblr_get_contact_by_url($hookData['uri']);

	// Authoritative probe should set the result even if the probe was unsuccessful
	if ($hookData['network'] == Protocol::TUMBLR && empty($hookData['result'])) {
		$hookData['result'] = [];
	}
}

function tumblr_item_by_link(array &$hookData)
{
	// Don't overwrite an existing result
	if (isset($hookData['item_id'])) {
		return;
	}

	if (!tumblr_enabled_for_user($hookData['uid'])) {
		return;
	}

	if (!preg_match('#^https?://www\.tumblr.com/blog/view/(.+)/(\d+).*#', $hookData['uri'], $matches) && !preg_match('#^https?://www\.tumblr.com/(.+)/(\d+).*#', $hookData['uri'], $matches)) {
		return;
	}

	Logger::debug('Found tumblr post', ['url' => $hookData['uri'], 'blog' => $matches[1], 'id' => $matches[2]]);

	$parameters = ['id' => $matches[2], 'reblog_info' => false, 'notes_info' => false, 'npf' => false];
	$result = tumblr_get($hookData['uid'], 'blog/' . $matches[1] . '/posts', $parameters);
	if ($result->meta->status > 399) {
		Logger::notice('Error fetching status', ['meta' => $result->meta, 'response' => $result->response, 'errors' => $result->errors, 'blog' => $matches[1], 'id' => $matches[2]]);
		return [];
	}

	Logger::debug('Got post', ['blog' => $matches[1], 'id' => $matches[2], 'result' => $result->response->posts]);
	if (!empty($result->response->posts)) {
		$hookData['item_id'] = tumblr_process_post($result->response->posts[0], $hookData['uid'], Item::PR_FETCHED);
	}
}

function tumblr_support_follow(array &$data)
{
	if ($data['protocol'] == Protocol::TUMBLR) {
		$data['result'] = true;
	}
}

function tumblr_support_probe(array &$data)
{
	if ($data['protocol'] == Protocol::TUMBLR) {
		$data['result'] = true;
	}
}

function tumblr_follow(array &$hook_data)
{
	$uid = DI::userSession()->getLocalUserId();

	if (!tumblr_enabled_for_user($uid)) {
		return;
	}

	Logger::debug('Check if contact is Tumblr', ['url' => $hook_data['url']]);

	$fields = tumblr_get_contact_by_url($hook_data['url']);
	if (empty($fields)) {
		Logger::debug('Contact is not a Tumblr contact', ['url' => $hook_data['url']]);
		return;
	}

	$result = tumblr_post($uid, 'user/follow', ['url' => $fields['url']]);
	if ($result->meta->status <= 399) {
		$hook_data['contact'] = $fields;
		Logger::debug('Successfully start following', ['url' => $fields['url']]);
	} else {
		Logger::notice('Following failed', ['meta' => $result->meta, 'response' => $result->response, 'errors' => $result->errors, 'url' => $fields['url']]);
	}
}

function tumblr_unfollow(array &$hook_data)
{
	if (!tumblr_enabled_for_user($hook_data['uid'])) {
		return;
	}

	if (!tumblr_get_contact_uuid($hook_data['contact'])) {
		return;
	}
	$result = tumblr_post($hook_data['uid'], 'user/unfollow', ['url' => $hook_data['contact']['url']]);
	$hook_data['result'] = ($result->meta->status <= 399);
}

function tumblr_block(array &$hook_data)
{
	if (!tumblr_enabled_for_user($hook_data['uid'])) {
		return;
	}

	$uuid = tumblr_get_contact_uuid($hook_data['contact']);
	if (!$uuid) {
		return;
	}

	$result = tumblr_post($hook_data['uid'], 'blog/' . tumblr_get_page($hook_data['uid']) . '/blocks', ['blocked_tumblelog' => $uuid]);
	$hook_data['result'] = ($result->meta->status <= 399);

	if ($hook_data['result']) {
		$cdata = Contact::getPublicAndUserContactID($hook_data['contact']['id'], $hook_data['uid']);
		if (!empty($cdata['user'])) {
			Contact::remove($cdata['user']);
		}
	}
}

function tumblr_unblock(array &$hook_data)
{
	if (!tumblr_enabled_for_user($hook_data['uid'])) {
		return;
	}

	$uuid = tumblr_get_contact_uuid($hook_data['contact']);
	if (!$uuid) {
		return;
	}

	$result = tumblr_delete($hook_data['uid'], 'blog/' . tumblr_get_page($hook_data['uid']) . '/blocks', ['blocked_tumblelog' => $uuid]);
	$hook_data['result'] = ($result->meta->status <= 399);
}

function tumblr_get_contact_uuid(array $contact): string
{
	if (($contact['network'] != Protocol::TUMBLR) || (substr($contact['poll'], 0, 8) != 'tumblr::')) {
		return '';
	}
	return substr($contact['poll'], 8);
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function tumblr_module()
{
}

function tumblr_content()
{
	if (!DI::userSession()->getLocalUserId()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Permission denied.'));
		return;
	}

	switch (DI::args()->getArgv()[1] ?? '') {
		case 'connect':
			tumblr_connect();
			break;

		case 'redirect':
			tumblr_redirect();
			break;
	}
	DI::baseUrl()->redirect('settings/connectors/tumblr');
}

function tumblr_redirect()
{
	if (($_REQUEST['state'] ?? '') != DI::session()->get('oauth_state')) {
		return;
	}

	tumblr_get_token(DI::userSession()->getLocalUserId(), $_REQUEST['code'] ?? '');
}

function tumblr_connect()
{
	// Define the needed keys
	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	if (empty($consumer_key) || empty($consumer_secret)) {
		return;
	}

	$state = base64_encode(random_bytes(20));
	DI::session()->set('oauth_state', $state);

	$parameters = [
		'client_id'     => $consumer_key,
		'response_type' => 'code',
		'scope'         => 'basic write offline_access',
		'state'         => $state
	];

	System::externalRedirect('https://www.tumblr.com/oauth2/authorize?' . http_build_query($parameters));
}

function tumblr_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/tumblr/');

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$consumer_key'    => ['consumer_key', DI::l10n()->t('Consumer Key'), DI::config()->get('tumblr', 'consumer_key'), ''],
		'$consumer_secret' => ['consumer_secret', DI::l10n()->t('Consumer Secret'), DI::config()->get('tumblr', 'consumer_secret'), ''],
		'$max_tags'        => ['max_tags', DI::l10n()->t('Maximum tags'), DI::config()->get('tumblr', 'max_tags') ?? TUMBLR_DEFAULT_MAXIMUM_TAGS, DI::l10n()->t('Maximum number of tags that a user can follow. Enter 0 to deactivate the feature.')],
	]);
}

function tumblr_addon_admin_post()
{
	DI::config()->set('tumblr', 'consumer_key', trim($_POST['consumer_key'] ?? ''));
	DI::config()->set('tumblr', 'consumer_secret', trim($_POST['consumer_secret'] ?? ''));
	DI::config()->set('tumblr', 'max_tags', max(0, intval($_POST['max_tags'] ?? '')));
}

function tumblr_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post') ?? false;
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default') ?? false;
	$import      = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'import') ?? false;
	$tags        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'tags') ?? [];

	$max_tags = DI::config()->get('tumblr', 'max_tags') ?? TUMBLR_DEFAULT_MAXIMUM_TAGS;

	$tags_str = implode(', ', $tags);
	$cachekey = 'tumblr-blogs-' . DI::userSession()->getLocalUserId();
	$blogs = DI::cache()->get($cachekey);
	if (empty($blogs)) {
		$blogs = tumblr_get_blogs(DI::userSession()->getLocalUserId());
		if (!empty($blogs)) {
			DI::cache()->set($cachekey, $blogs, Duration::HALF_HOUR);
		}
	}

	if (!empty($blogs)) {
		$page = tumblr_get_page(DI::userSession()->getLocalUserId(), $blogs);

		$page_select = ['tumblr_page', DI::l10n()->t('Post to page:'), $page, '', $blogs];
	}

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/tumblr/');
	$html = Renderer::replaceMacros($t, [
		'$l10n' => [
			'connect'   => DI::l10n()->t('(Re-)Authenticate your tumblr page'),
			'noconnect' => DI::l10n()->t('You are not authenticated to tumblr'),
		],

		'$authenticate_url' => DI::baseUrl() . '/tumblr/connect',

		'$enable'      => ['tumblr', DI::l10n()->t('Enable Tumblr Post Addon'), $enabled],
		'$bydefault'   => ['tumblr_bydefault', DI::l10n()->t('Post to Tumblr by default'), $def_enabled],
		'$import'      => ['tumblr_import', DI::l10n()->t('Import the remote timeline'), $import],
		'$tags'        => ['tags', DI::l10n()->t('Subscribed tags'), $tags_str, DI::l10n()->t('Comma separated list of up to %d tags that will be imported additionally to the timeline', $max_tags)],
		'$page_select' => $page_select ?? '',
	]);

	$data = [
		'connector' => 'tumblr',
		'title'     => DI::l10n()->t('Tumblr Import/Export'),
		'image'     => 'images/tumblr.png',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}

function tumblr_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'tumblr_enable',
				DI::l10n()->t('Post to Tumblr'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default')
			]
		];
	}
}

function tumblr_settings_post(array &$b)
{
	if (!empty($_POST['tumblr-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'post',            intval($_POST['tumblr']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'page',            $_POST['tumblr_page']);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default', intval($_POST['tumblr_bydefault']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'import',          intval($_POST['tumblr_import']));

		$max_tags = DI::config()->get('tumblr', 'max_tags') ?? TUMBLR_DEFAULT_MAXIMUM_TAGS;
		$tags     = [];
		foreach (explode(',', $_POST['tags']) as $tag) {
			if (count($tags) < $max_tags) {
				$tags[] = trim($tag, ' #');
			}
		}

		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'tags', $tags);
	}
}

function tumblr_cron()
{
	$last = DI::keyValue()->get('tumblr_last_poll');

	$poll_interval = intval(DI::config()->get('tumblr', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = TUMBLR_DEFAULT_POLL_INTERVAL;
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

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'tumblr', 'k' => 'import', 'v' => true]);
	foreach ($pconfigs as $pconfig) {
		if ($abandon_days != 0) {
			if (!DBA::exists('user', ["`uid` = ? AND `login_date` >= ?", $pconfig['uid'], $abandon_limit])) {
				Logger::notice('abandoned account: timeline from user will not be imported', ['user' => $pconfig['uid']]);
				continue;
			}
		}

		Logger::notice('importing timeline - start', ['user' => $pconfig['uid']]);
		tumblr_fetch_dashboard($pconfig['uid']);
		tumblr_fetch_tags($pconfig['uid']);
		Logger::notice('importing timeline - done', ['user' => $pconfig['uid']]);
	}

	$last_clean = DI::keyValue()->get('tumblr_last_clean');
	if (empty($last_clean) || ($last_clean + 86400 < time())) {
		Logger::notice('Start contact cleanup');
		$contacts = DBA::select('account-user-view', ['id', 'pid'], ["`network` = ? AND `uid` != ? AND `rel` = ?", Protocol::TUMBLR, 0, Contact::NOTHING]);
		while ($contact = DBA::fetch($contacts)) {
			Worker::add(Worker::PRIORITY_LOW, 'MergeContact', $contact['pid'], $contact['id'], 0);
		}
		DBA::close($contacts);
		DI::keyValue()->set('tumblr_last_clean', time());
		Logger::notice('Contact cleanup done');
	}

	Logger::notice('cron_end');

	DI::keyValue()->set('tumblr_last_poll', time());
}

function tumblr_hook_fork(array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	// Editing is not supported by the addon
	if (($post['created'] !== $post['edited']) && !$post['deleted']) {
		DI::logger()->info('Editing is not supported by the addon');
		$b['execute'] = false;
		return;
	}

	if (DI::pConfig()->get($post['uid'], 'tumblr', 'import')) {
		// Don't post if it isn't a reply to a tumblr post
		if (($post['parent'] != $post['id']) && !Post::exists(['id' => $post['parent'], 'network' => Protocol::TUMBLR])) {
			Logger::notice('No tumblr parent found', ['item' => $post['id']]);
			$b['execute'] = false;
			return;
		}
	} elseif (!strstr($post['postopts'] ?? '', 'tumblr') || ($post['parent'] != $post['id']) || $post['private']) {
		DI::logger()->info('Activities are never exported when we don\'t import the tumblr timeline', ['uid' => $post['uid']]);
		$b['execute'] = false;
		return;
	}
}

function tumblr_post_local(array &$b)
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

	$tmbl_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post'));
	$tmbl_enable = (($tmbl_post && !empty($_REQUEST['tumblr_enable'])) ? intval($_REQUEST['tumblr_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default'))) {
		$tmbl_enable = 1;
	}

	if (!$tmbl_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'tumblr';
}

function tumblr_send(array &$b)
{
	if (($b['created'] !== $b['edited']) && !$b['deleted']) {
		return;
	}

	if ($b['gravity'] != Item::GRAVITY_PARENT) {
		Logger::debug('Got comment', ['item' => $b]);

		$parent = tumblr_get_post_from_uri($b['thr-parent']);
		if (empty($parent)) {
			Logger::notice('No tumblr post', ['thr-parent' => $b['thr-parent']]);
			return;
		}

		Logger::debug('Parent found', ['parent' => $parent]);

		$page = tumblr_get_page($b['uid']);

		if ($b['gravity'] == Item::GRAVITY_COMMENT) {
			Logger::notice('Commenting is not supported (yet)');
		} else {
			if (($b['verb'] == Activity::LIKE) && !$b['deleted']) {
				$params = ['id' => $parent['id'], 'reblog_key' => $parent['reblog_key']];
				$result = tumblr_post($b['uid'], 'user/like', $params);
			} elseif (($b['verb'] == Activity::LIKE) && $b['deleted']) {
				$params = ['id' => $parent['id'], 'reblog_key' => $parent['reblog_key']];
				$result = tumblr_post($b['uid'], 'user/unlike', $params);
			} elseif (($b['verb'] == Activity::ANNOUNCE) && !$b['deleted']) {
				$params = ['id' => $parent['id'], 'reblog_key' => $parent['reblog_key']];
				$result = tumblr_post($b['uid'], 'blog/' . $page . '/post/reblog', $params);
			} elseif (($b['verb'] == Activity::ANNOUNCE) && $b['deleted']) {
				$announce = tumblr_get_post_from_uri($b['extid']);
				if (empty($announce)) {
					return;
				}
				$params = ['id' => $announce['id']];
				$result = tumblr_post($b['uid'], 'blog/' . $page . '/post/delete', $params);
			} else {
				// Unsupported activity
				return;
			}

			if ($result->meta->status < 400) {
				Logger::info('Successfully performed activity', ['verb' => $b['verb'], 'deleted' => $b['deleted'], 'meta' => $result->meta, 'response' => $result->response]);
				if (!$b['deleted'] && !empty($result->response->id_string)) {
					Item::update(['extid' => 'tumblr::' . $result->response->id_string], ['id' => $b['id']]);
				}
			} else {
				Logger::notice('Error while performing activity', ['verb' => $b['verb'], 'deleted' => $b['deleted'], 'meta' => $result->meta, 'response' => $result->response, 'errors' => $result->errors, 'params' => $params]);
			}
		}
		return;
	} elseif ($b['private'] || !strstr($b['postopts'], 'tumblr')) {
		return;
	}

	if (!tumblr_send_npf($b)) {
		tumblr_send_legacy($b);
	}
}

function tumblr_send_legacy(array $b)
{
	$b['body'] = BBCode::removeAttachment($b['body']);

	$title = trim($b['title']);

	$media = Post\Media::getByURIId($b['uri-id'], [Post\Media::HTML, Post\Media::AUDIO, Post\Media::VIDEO, Post\Media::IMAGE]);

	$photo = array_search(Post\Media::IMAGE, array_column($media, 'type'));
	$link  = array_search(Post\Media::HTML, array_column($media, 'type'));
	$audio = array_search(Post\Media::AUDIO, array_column($media, 'type'));
	$video = array_search(Post\Media::VIDEO, array_column($media, 'type'));

	$params = [
		'state'  => 'published',
		'tags'   => implode(',', array_column(Tag::getByURIId($b['uri-id']), 'name')),
		'tweet'  => 'off',
		'format' => 'html',
	];

	$body = BBCode::removeShareInformation($b['body']);
	$body = Post\Media::removeFromEndOfBody($body);

	if ($photo !== false) {
		$params['type'] = 'photo';
		$params['caption'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
		$params['data'] = [];
		foreach ($media as $photo) {
			if ($photo['type'] == Post\Media::IMAGE) {
				if (Network::isLocalLink($photo['url']) && ($data = Photo::getResourceData($photo['url']))) {
					$photo = Photo::selectFirst([], ["`resource-id` = ? AND `scale` > ?", $data['guid'], 0]);
					if (!empty($photo)) {
						$params['data'][] = Photo::getImageDataForPhoto($photo);
					}
				}
			}
		}
	} elseif ($link !== false) {
		$params['type']        = 'link';
		$params['title']       = $media[$link]['name'];
		$params['url']         = $media[$link]['url'];
		$params['description'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);

		if (!empty($media[$link]['preview'])) {
			$params['thumbnail'] = $media[$link]['preview'];
		}
		if (!empty($media[$link]['description'])) {
			$params['excerpt'] = $media[$link]['description'];
		}
		if (!empty($media[$link]['author-name'])) {
			$params['author'] = $media[$link]['author-name'];
		}
	} elseif ($audio !== false) {
		$params['type']         = 'audio';
		$params['external_url'] = $media[$audio]['url'];
		$params['caption']      = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
	} elseif ($video !== false) {
		$params['type']    = 'video';
		$params['embed']   = $media[$video]['url'];
		$params['caption'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
	} else {
		$params['type']  = 'text';
		$params['title'] = $title;
		$params['body']  = BBCode::convertForUriId($b['uri-id'], $b['body'], BBCode::CONNECTORS);
	}

	if (isset($params['caption']) && (trim($title) != '')) {
		$params['caption'] = '<h1>' . $title . '</h1>' .
			'<p>' . $params['caption'] . '</p>';
	}

	$page = tumblr_get_page($b['uid']);

	$result = tumblr_post($b['uid'], 'blog/' . $page . '/post', $params);

	if ($result->meta->status < 400) {
		Logger::info('Success (legacy)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response]);
	} else {
		Logger::notice('Error posting blog (legacy)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response, 'errors' => $result->errors, 'params' => $params]);
	}
}

function tumblr_send_npf(array $post): bool
{
	$page = tumblr_get_page($post['uid']);

	if (empty($page)) {
		Logger::notice('Missing page, post will not be send to Tumblr.', ['uid' => $post['uid'], 'page' => $page, 'id' => $post['id']]);
		// "true" is returned, since the legacy function will fail as well.
		return true;
	}

	$post['body'] = Post\Media::addAttachmentsToBody($post['uri-id'], $post['body']);
	if (!empty($post['title'])) {
		$post['body'] = '[h1]' . $post['title'] . "[/h1]\n" . $post['body'];
	}

	$params = [
		'content'                => NPF::fromBBCode($post['body'], $post['uri-id']),
		'state'                  => 'published',
		'date'                   => DateTimeFormat::utc($post['created'], DateTimeFormat::ATOM),
		'tags'                   => implode(',', array_column(Tag::getByURIId($post['uri-id']), 'name')),
		'is_private'             => false,
		'interactability_reblog' => 'everyone'
	];

	$result = tumblr_post($post['uid'], 'blog/' . $page . '/posts', $params);

	if ($result->meta->status < 400) {
		Logger::info('Success (NPF)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response]);
		return true;
	} else {
		Logger::notice('Error posting blog (NPF)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response, 'errors' => $result->errors, 'params' => $params]);
		return false;
	}
}

function tumblr_get_post_from_uri(string $uri): array
{
	$parts = explode(':', $uri);
	if (($parts[0] != 'tumblr') || empty($parts[2])) {
		return [];
	}

	$post['id']        = $parts[2];
	$post['reblog_key'] = $parts[3] ?? '';

	$post['reblog_key'] = str_replace('@t', '', $post['reblog_key']); // Temp
	return $post;
}

/**
 * Fetch posts for user defined hashtags for the given user
 *
 * @param integer $uid
 * @return void
 */
function tumblr_fetch_tags(int $uid)
{
	if (!DI::config()->get('tumblr', 'max_tags') ?? TUMBLR_DEFAULT_MAXIMUM_TAGS) {
		return;
	}

	foreach (DI::pConfig()->get($uid, 'tumblr', 'tags') ?? [] as $tag) {
		$data = tumblr_get($uid, 'tagged', ['tag' => $tag]);
		foreach (array_reverse($data->response) as $post) {
			$id = tumblr_process_post($post, $uid, Item::PR_TAG);
			if (!empty($id)) {
				Logger::debug('Tag post imported', ['tag' => $tag, 'id' => $id]);
				$post = Post::selectFirst(['uri-id'], ['id' => $id]);
				$stored = Post\Category::storeFileByURIId($post['uri-id'], $uid, Post\Category::SUBCRIPTION, $tag);
				Logger::debug('Stored tag subscription for user', ['uri-id' => $post['uri-id'], 'uid' => $uid, 'tag' => $tag, 'stored' => $stored]);
			}
		}
	}
}

/**
 * Fetch the dashboard (timeline) for the given user
 *
 * @param integer $uid
 * @return void
 */
function tumblr_fetch_dashboard(int $uid)
{
	$parameters = ['reblog_info' => false, 'notes_info' => false, 'npf' => false];

	$last = DI::pConfig()->get($uid, 'tumblr', 'last_id');
	if (!empty($last)) {
		$parameters['since_id'] = $last;
	}

	$dashboard = tumblr_get($uid, 'user/dashboard', $parameters);
	if ($dashboard->meta->status > 399) {
		Logger::notice('Error fetching dashboard', ['meta' => $dashboard->meta, 'response' => $dashboard->response, 'errors' => $dashboard->errors]);
		return [];
	}

	if (empty($dashboard->response->posts)) {
		return;
	}

	foreach (array_reverse($dashboard->response->posts) as $post) {
		if ($post->id > $last) {
			$last = $post->id;
		}

		Logger::debug('Importing post', ['uid' => $uid, 'created' => date(DateTimeFormat::MYSQL, $post->timestamp), 'id' => $post->id_string]);

		tumblr_process_post($post, $uid, Item::PR_NONE);

		DI::pConfig()->set($uid, 'tumblr', 'last_id', $last);
	}
}

function tumblr_process_post(stdClass $post, int $uid, int $post_reason): int
{
	$uri = 'tumblr::' . $post->id_string . ':' . $post->reblog_key;

	if (Post::exists(['uri' => $uri, 'uid' => $uid]) || ($post->blog->uuid == tumblr_get_page($uid))) {
		return 0;
	}

	$item = tumblr_get_header($post, $uri, $uid);

	$item = tumblr_get_content($item, $post);

	$item['post-reason'] = $post_reason;

	if (!empty($post->followed)) {
		$item['post-reason'] = Item::PR_FOLLOWER;
	}

	$id = item::insert($item);

	if ($id) {
		$stored = Post::selectFirst(['uri-id'], ['id' => $id]);

		if (!empty($post->tags)) {
			foreach ($post->tags as $tag) {
				Tag::store($stored['uri-id'], Tag::HASHTAG, $tag);
			}
		}
	}
	return $id;
}

/**
 * Sets the initial data for the item array
 *
 * @param stdClass $post
 * @param string $uri
 * @param integer $uid
 * @return array
 */
function tumblr_get_header(stdClass $post, string $uri, int $uid): array
{
	$contact = tumblr_get_contact($post->blog, $uid);
	$item = [
		'network'       => Protocol::TUMBLR,
		'uid'           => $uid,
		'wall'          => false,
		'uri'           => $uri,
		'private'       => Item::UNLISTED,
		'verb'          => Activity::POST,
		'contact-id'    => $contact['id'],
		'author-name'   => $contact['name'],
		'author-link'   => $contact['url'],
		'author-avatar' => $contact['avatar'],
		'plink'         => $post->post_url,
		'created'       => date(DateTimeFormat::MYSQL, $post->timestamp)
	];

	$item['owner-name']   = $item['author-name'];
	$item['owner-link']   = $item['author-link'];
	$item['owner-avatar'] = $item['author-avatar'];

	return $item;
}

/**
 * Set the body according the given content type
 *
 * @param array $item
 * @param stdClass $post
 * @return array
 */
function tumblr_get_content(array $item, stdClass $post): array
{
	switch ($post->type) {
		case 'text':
			$item['title'] = $post->title;
			$item['body'] = HTML::toBBCode(tumblr_add_npf_data($post->body, $post->post_url));
			break;

		case 'quote':
			if (empty($post->text)) {
				$body = HTML::toBBCode($post->text) . "\n";
			} else {
				$body = '';
			}
			if (!empty($post->source_title) && !empty($post->source_url)) {
				$body .= '[url=' . $post->source_url . ']' . $post->source_title . "[/url]:\n";
			} elseif (!empty($post->source_title)) {
				$body .= $post->source_title . ":\n";
			}
			$body .= '[quote]' . HTML::toBBCode($post->source) . '[/quote]';
			$item['body'] = $body;
			break;

		case 'link':
			$item['body'] = HTML::toBBCode($post->description) . "\n" . PageInfo::getFooterFromUrl($post->url);
			break;

		case 'answer':
			if (!empty($post->asking_name) && !empty($post->asking_url)) {
				$body = '[url=' . $post->asking_url . ']' . $post->asking_name . "[/url]:\n";
			} elseif (!empty($post->asking_name)) {
				$body = $post->asking_name . ":\n";
			} else {
				$body = '';
			}
			$body .= '[quote]' . HTML::toBBCode($post->question) . "[/quote]\n" . HTML::toBBCode($post->answer);
			$item['body'] = $body;
			break;

		case 'video':
			$item['body'] = HTML::toBBCode($post->caption);
			if (!empty($post->video_url)) {
				$item['body'] .= "\n[video]" . $post->video_url . "[/video]\n";
			} elseif (!empty($post->thumbnail_url)) {
				$item['body'] .= "\n[url=" . $post->permalink_url . "][img]" . $post->thumbnail_url . "[/img][/url]\n";
			} elseif (!empty($post->permalink_url)) {
				$item['body'] .= "\n[url]" . $post->permalink_url . "[/url]\n";
			} elseif (!empty($post->source_url) && !empty($post->source_title)) {
				$item['body'] .= "\n[url=" . $post->source_url . "]" . $post->source_title . "[/url]\n";
			} elseif (!empty($post->source_url)) {
				$item['body'] .= "\n[url]" . $post->source_url . "[/url]\n";
			}
			break;

		case 'audio':
			$item['body'] = HTML::toBBCode($post->caption);
			if (!empty($post->source_url) && !empty($post->source_title)) {
				$item['body'] .= "\n[url=" . $post->source_url . "]" . $post->source_title . "[/url]\n";
			} elseif (!empty($post->source_url)) {
				$item['body'] .= "\n[url]" . $post->source_url . "[/url]\n";
			}
			break;

		case 'photo':
			$item['body'] = HTML::toBBCode($post->caption);
			foreach ($post->photos as $photo) {
				if (!empty($photo->original_size)) {
					$item['body'] .= "\n[img]" . $photo->original_size->url . "[/img]";
				} elseif (!empty($photo->alt_sizes)) {
					$item['body'] .= "\n[img]" . $photo->alt_sizes[0]->url . "[/img]";
				}
			}
			break;

		case 'chat':
			$item['title'] = $post->title;
			$item['body']  = "\n[ul]";
			foreach ($post->dialogue as $line) {
				$item['body'] .= "\n[li]" . $line->label . " " . $line->phrase . "[/li]";
			}
			$item['body'] .= "[/ul]\n";
			break;
	}
	return $item;
}

function tumblr_add_npf_data(string $html, string $plink): string
{
	$doc = new DOMDocument();

	$doc->formatOutput = true;
	@$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
	$xpath = new DomXPath($doc);
	$list = $xpath->query('//p[@class="npf_link"]');
	foreach ($list as $node) {
		$data = tumblr_get_npf_data($node);
		if (empty($data)) {
			continue;
		}

		tumblr_replace_with_npf($doc, $node, tumblr_get_type_replacement($data, $plink));
	}

	$list = $xpath->query('//div[@data-npf]');
	foreach ($list as $node) {
		$data = tumblr_get_npf_data($node);
		if (empty($data)) {
			continue;
		}

		tumblr_replace_with_npf($doc, $node, tumblr_get_type_replacement($data, $plink));
	}

	$list = $xpath->query('//figure[@data-provider="youtube"]');
	foreach ($list as $node) {
		$attributes = tumblr_get_attributes($node);
		if (empty($attributes['data-url'])) {
			continue;
		}
		tumblr_replace_with_npf($doc, $node, '[youtube]' . $attributes['data-url'] . '[/youtube]');
	}

	$list = $xpath->query('//figure[@data-npf]');
	foreach ($list as $node) {
		$data = tumblr_get_npf_data($node);
		if (empty($data)) {
			continue;
		}
		tumblr_replace_with_npf($doc, $node, tumblr_get_type_replacement($data, $plink));
	}

	return $doc->saveHTML();
}

function tumblr_replace_with_npf(DOMDocument $doc, DOMNode $node, string $replacement)
{
	if (empty($replacement)) {
		return;
	}
	$replace = $doc->createTextNode($replacement);
	$node->parentNode->insertBefore($replace, $node);
	$node->parentNode->removeChild($node);
}

function tumblr_get_npf_data(DOMNode $node): array
{
	$attributes = tumblr_get_attributes($node);
	if (empty($attributes['data-npf'])) {
		return [];
	}

	return json_decode($attributes['data-npf'], true);
}

function tumblr_get_attributes($node): array
{
	if (empty($node->attributes)) {
		return [];
	}

	$attributes = [];
	foreach ($node->attributes as $key => $attribute) {
		$attributes[$key] = trim($attribute->value);
	}
	return $attributes;
}

function tumblr_get_type_replacement(array $data, string $plink): string
{
	switch ($data['type']) {
		case 'poll':
			$body = '[p][url=' . $plink . ']' . $data['question'] . '[/url][/p][ul]';
			foreach ($data['answers'] as $answer) {
				$body .= '[li]' . $answer['answer_text'] . '[/li]';
			}
			$body .= '[/ul]';
			break;

		case 'link':
			$body = PageInfo::getFooterFromUrl(str_replace('https://href.li/?', '', $data['url']));
			break;

		case 'video':
			if (!empty($data['url']) && ($data['provider'] == 'tumblr')) {
				$body = '[video]' . $data['url'] . '[/video]';
				break;
			}

		default:
			Logger::notice('Unknown type', ['type' => $data['type'], 'data' => $data, 'plink' => $plink]);
			$body = '';
	}

	return $body;
}

/**
 * Get a contact array for the given blog
 *
 * @param stdClass $blog
 * @param integer $uid
 * @return array
 */
function tumblr_get_contact(stdClass $blog, int $uid): array
{
	$condition = ['network' => Protocol::TUMBLR, 'uid' => 0, 'poll' => 'tumblr::' . $blog->uuid];
	$contact = Contact::selectFirst(['id', 'updated'], $condition);

	$update = empty($contact) || $contact['updated'] < DateTimeFormat::utc('now -24 hours');

	$public_fields = $fields = tumblr_get_contact_fields($blog, $uid, $update);

	$avatar = $fields['avatar'] ?? '';
	unset($fields['avatar']);
	unset($public_fields['avatar']);

	$public_fields['uid'] = 0;
	$public_fields['rel'] = Contact::NOTHING;

	if (empty($contact)) {
		$cid = Contact::insert($public_fields);
	} else {
		$cid = $contact['id'];
		Contact::update($public_fields, ['id' => $cid], true);
	}

	if ($uid != 0) {
		$condition = ['network' => Protocol::TUMBLR, 'uid' => $uid, 'poll' => 'tumblr::' . $blog->uuid];

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

	if (!empty($avatar)) {
		Contact::updateAvatar($cid, $avatar);
	}

	return Contact::getById($cid);
}

function tumblr_get_contact_fields(stdClass $blog, int $uid, bool $update): array
{
	$baseurl = 'https://tumblr.com';
	$url     = $baseurl . '/' . $blog->name;

	$fields = [
		'uid'      => $uid,
		'network'  => Protocol::TUMBLR,
		'poll'     => 'tumblr::' . $blog->uuid,
		'baseurl'  => $baseurl,
		'priority' => 1,
		'writable' => true,
		'blocked'  => false,
		'readonly' => false,
		'pending'  => false,
		'url'      => $url,
		'nurl'     => Strings::normaliseLink($url),
		'alias'    => $blog->url,
		'name'     => $blog->title ?: $blog->name,
		'nick'     => $blog->name,
		'addr'     => $blog->name . '@tumblr.com',
		'about'    => HTML::toBBCode($blog->description),
		'updated'  => date(DateTimeFormat::MYSQL, $blog->updated)
	];

	if (!$update) {
		Logger::debug('Got contact fields', ['uid' => $uid, 'url' => $fields['url']]);
		return $fields;
	}

	$info = tumblr_get($uid, 'blog/' . $blog->uuid . '/info');
	if ($info->meta->status > 399) {
		Logger::notice('Error fetching blog info', ['meta' => $info->meta, 'response' => $info->response, 'errors' => $info->errors]);
		return $fields;
	}

	$avatar = $info->response->blog->avatar;
	if (!empty($avatar)) {
		$fields['avatar'] = $avatar[0]->url;
	}

	if ($info->response->blog->followed && $info->response->blog->subscribed) {
		$fields['rel'] = Contact::FRIEND;
	} elseif ($info->response->blog->followed && !$info->response->blog->subscribed) {
		$fields['rel'] = Contact::SHARING;
	} elseif (!$info->response->blog->followed && $info->response->blog->subscribed) {
		$fields['rel'] = Contact::FOLLOWER;
	} else {
		$fields['rel'] = Contact::NOTHING;
	}

	$fields['header'] = $info->response->blog->theme->header_image_focused;

	Logger::debug('Got updated contact fields', ['uid' => $uid, 'url' => $fields['url']]);
	return $fields;
}

/**
 * Get the default page for posting. Detects the value if not provided or has got a bad value.
 *
 * @param integer $uid
 * @param array $blogs
 * @return string
 */
function tumblr_get_page(int $uid, array $blogs = []): string
{
	$page = DI::pConfig()->get($uid, 'tumblr', 'page');

	if (!empty($page) && (strpos($page, '/') === false)) {
		return $page;
	}

	if (empty($blogs)) {
		$blogs = tumblr_get_blogs($uid);
	}

	if (!empty($blogs)) {
		$page = array_key_first($blogs);
		DI::pConfig()->set($uid, 'tumblr', 'page', $page);
		return $page;
	}

	return '';
}

/**
 * Get an array of blogs for the given user
 *
 * @param integer $uid
 * @return array
 */
function tumblr_get_blogs(int $uid): array
{
	$userinfo = tumblr_get($uid, 'user/info');
	if ($userinfo->meta->status > 299) {
		Logger::notice('Error fetching blogs', ['meta' => $userinfo->meta, 'response' => $userinfo->response, 'errors' => $userinfo->errors]);
		return [];
	}

	$blogs = [];
	foreach ($userinfo->response->user->blogs as $blog) {
		$blogs[$blog->uuid] = $blog->name;
	}
	return $blogs;
}

function tumblr_enabled_for_user(int $uid)
{
	return !empty($uid) && !empty(DI::pConfig()->get($uid, 'tumblr', 'access_token')) &&
		!empty(DI::pConfig()->get($uid, 'tumblr', 'refresh_token')) &&
		!empty(DI::config()->get('tumblr', 'consumer_key')) &&
		!empty(DI::config()->get('tumblr', 'consumer_secret'));
}

/**
 * Get a contact array from a Tumblr url
 *
 * @param string $url
 * @return array|null
 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
 */
function tumblr_get_contact_by_url(string $url): ?array
{
	$consumer_key = DI::config()->get('tumblr', 'consumer_key');
	if (empty($consumer_key)) {
		return null;
	}

	if (!preg_match('#^https?://tumblr.com/(.+)#', $url, $matches) && !preg_match('#^https?://www\.tumblr.com/(.+)#', $url, $matches) && !preg_match('#^https?://(.+)\.tumblr.com#', $url, $matches)) {
		try {
			$curlResult = DI::httpClient()->get($url);
		} catch (\Exception $e) {
			return null;
		}
		$html = $curlResult->getBody();
		if (empty($html)) {
			return null;
		}
		$doc = new DOMDocument();
		@$doc->loadHTML($html);
		$xpath = new DomXPath($doc);
		$body = $xpath->query('body');
		$attributes = tumblr_get_attributes($body->item(0));
		$blog = $attributes['data-urlencoded-name'] ?? '';
	} else {
		$blogs = explode('/', $matches[1]);
		$blog = $blogs[0] ?? '';
	}

	if (empty($blog)) {
		return null;
	}

	Logger::debug('Update Tumblr blog data', ['url' => $url]);

	$curlResult = DI::httpClient()->get('https://api.tumblr.com/v2/blog/' . $blog . '/info?api_key=' . $consumer_key);
	$body = $curlResult->getBody();
	$data = json_decode($body);
	if (empty($data)) {
		return null;
	}

	if (is_array($data->response->blog) || empty($data->response->blog)) {
		Logger::warning('Unexpected blog format', ['blog' => $blog, 'data' => $data]);
		return null;
	}

	$baseurl = 'https://tumblr.com';
	$url     = $baseurl . '/' . $data->response->blog->name;

	return [
		'url'      => $url,
		'nurl'     => Strings::normaliseLink($url),
		'addr'     => $data->response->blog->name . '@tumblr.com',
		'alias'    => $data->response->blog->url,
		'batch'    => '',
		'notify'   => '',
		'poll'     => 'tumblr::' . $data->response->blog->uuid,
		'poco'     => '',
		'name'     => $data->response->blog->title ?: $data->response->blog->name,
		'nick'     => $data->response->blog->name,
		'network'  => Protocol::TUMBLR,
		'baseurl'  => $baseurl,
		'pubkey'   => '',
		'priority' => 0,
		'guid'     => $data->response->blog->uuid,
		'about'    => HTML::toBBCode($data->response->blog->description),
		'photo'    => $data->response->blog->avatar[0]->url,
		'header'   => $data->response->blog->theme->header_image_focused,
	];
}

/**
 * Perform an OAuth2 GET request
 *
 * @param integer $uid
 * @param string $url
 * @param array $parameters
 * @return stdClass
 */
function tumblr_get(int $uid, string $url, array $parameters = []): stdClass
{
	$url = 'https://api.tumblr.com/v2/' . $url;

	if (!empty($parameters)) {
		$url .= '?' . http_build_query($parameters);
	}

	$curlResult = DI::httpClient()->get($url, HttpClientAccept::JSON, [HttpClientOptions::HEADERS => ['Authorization' => ['Bearer ' . tumblr_get_token($uid)]]]);
	return tumblr_format_result($curlResult);
}

/**
 * Perform an OAuth2 POST request
 *
 * @param integer $uid
 * @param string $url
 * @param array $parameters
 * @return stdClass
 */
function tumblr_post(int $uid, string $url, array $parameters): stdClass
{
	$url = 'https://api.tumblr.com/v2/' . $url;

	$curlResult = DI::httpClient()->post($url, $parameters, ['Authorization' => ['Bearer ' . tumblr_get_token($uid)]]);
	return tumblr_format_result($curlResult);
}

/**
 * Perform an OAuth2 DELETE request
 *
 * @param integer $uid
 * @param string $url
 * @param array $parameters
 * @return stdClass
 */
function tumblr_delete(int $uid, string $url, array $parameters): stdClass
{
	$url = 'https://api.tumblr.com/v2/' . $url;

	$opts = [
		HttpClientOptions::HEADERS     => ['Authorization' => ['Bearer ' . tumblr_get_token($uid)]],
		HttpClientOptions::FORM_PARAMS => $parameters
	];

	$curlResult = DI::httpClient()->request('delete', $url, $opts);
	return tumblr_format_result($curlResult);
}

/**
 * Format the get/post result value
 *
 * @param ICanHandleHttpResponses $curlResult
 * @return stdClass
 */
function tumblr_format_result(ICanHandleHttpResponses $curlResult): stdClass
{
	$result = json_decode($curlResult->getBody());
	if (empty($result) || empty($result->meta)) {
		$result               = new stdClass;
		$result->meta         = new stdClass;
		$result->meta->status = 500;
		$result->meta->msg    = '';
		$result->response     = [];
		$result->errors       = [];
	}
	return $result;
}

/**
 * Fetch the OAuth token, update it if needed
 *
 * @param integer $uid
 * @param string $code
 * @return string
 */
function tumblr_get_token(int $uid, string $code = ''): string
{
	$access_token  = DI::pConfig()->get($uid, 'tumblr', 'access_token');
	$expires_at    = DI::pConfig()->get($uid, 'tumblr', 'expires_at');
	$refresh_token = DI::pConfig()->get($uid, 'tumblr', 'refresh_token');

	if (empty($code) && !empty($access_token) && ($expires_at > (time()))) {
		Logger::debug('Got token', ['uid' => $uid, 'expires_at' => date('c', $expires_at)]);
		return $access_token;
	}

	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	$parameters = ['client_id' => $consumer_key, 'client_secret' => $consumer_secret];

	if (empty($refresh_token) && empty($code)) {
		$result = tumblr_exchange_token($uid);
		if (empty($result->refresh_token)) {
			Logger::info('Invalid result while exchanging token', ['uid' => $uid]);
			return '';
		}
		$expires_at = time() + $result->expires_in;
		Logger::debug('Updated token from OAuth1 to OAuth2', ['uid' => $uid, 'expires_at' => date('c', $expires_at)]);
	} else {
		if (!empty($code)) {
			$parameters['code']       = $code;
			$parameters['grant_type'] = 'authorization_code';
		} else {
			$parameters['refresh_token'] = $refresh_token;
			$parameters['grant_type']    = 'refresh_token';
		}

		$curlResult = DI::httpClient()->post('https://api.tumblr.com/v2/oauth2/token', $parameters);
		if (!$curlResult->isSuccess()) {
			Logger::info('Error fetching token', ['uid' => $uid, 'code' => $code, 'result' => $curlResult->getBody(), 'parameters' => $parameters]);
			return '';
		}

		$result = json_decode($curlResult->getBody());
		if (empty($result)) {
			Logger::info('Invalid result when updating token', ['uid' => $uid]);
			return '';
		}

		$expires_at = time() + $result->expires_in;
		Logger::debug('Renewed token', ['uid' => $uid, 'expires_at' => date('c', $expires_at)]);
	}

	DI::pConfig()->set($uid, 'tumblr', 'access_token', $result->access_token);
	DI::pConfig()->set($uid, 'tumblr', 'expires_at', $expires_at);
	DI::pConfig()->set($uid, 'tumblr', 'refresh_token', $result->refresh_token);

	return $result->access_token;
}

/**
 * Create an OAuth2 token out of an OAuth1 token
 *
 * @param int $uid
 * @return stdClass
 */
function tumblr_exchange_token(int $uid): stdClass
{
	$oauth_token        = DI::pConfig()->get($uid, 'tumblr', 'oauth_token');
	$oauth_token_secret = DI::pConfig()->get($uid, 'tumblr', 'oauth_token_secret');

	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	$stack = HandlerStack::create();

	$middleware = new Oauth1([
		'consumer_key'    => $consumer_key,
		'consumer_secret' => $consumer_secret,
		'token'           => $oauth_token,
		'token_secret'    => $oauth_token_secret
	]);

	$stack->push($middleware);

	try {
		$client = new Client([
			'base_uri' => 'https://api.tumblr.com/v2/',
			'handler' => $stack
		]);

		$response = $client->post('oauth2/exchange', ['auth' => 'oauth']);
		return json_decode($response->getBody()->getContents());
	} catch (RequestException $exception) {
		Logger::notice('Exchange failed', ['code' => $exception->getCode(), 'message' => $exception->getMessage()]);
		return new stdClass;
	}
}
