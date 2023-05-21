<?php
/**
 * Name: Bluesky Connector
 * Description: Post to Bluesky
 * Version: 1.0
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Photo;
use Friendica\Network\HTTPClient\Client\HttpClientAccept;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;
use Friendica\Util\DateTimeFormat;

function bluesky_install()
{
	Hook::register('load_config',             __FILE__, 'bluesky_load_config');
	Hook::register('hook_fork',               __FILE__, 'bluesky_hook_fork');
	Hook::register('post_local',              __FILE__, 'bluesky_post_local');
	Hook::register('notifier_normal',         __FILE__, 'bluesky_send');
	Hook::register('jot_networks',            __FILE__, 'bluesky_jot_nets');
	Hook::register('connector_settings',      __FILE__, 'bluesky_settings');
	Hook::register('connector_settings_post', __FILE__, 'bluesky_settings_post');
}

function bluesky_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('bluesky'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function bluesky_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post') ?? false;
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'post_by_default') ?? false;
	$host        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'host') ?: 'https://bsky.social';
	$handle      = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'handle');
	$did         = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'did');
	$username    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'bluesky', 'username');

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/bluesky/');
	$html = Renderer::replaceMacros($t, [
		'$enable'    => ['bluesky', DI::l10n()->t('Enable Bluesky Post Addon'), $enabled],
		'$bydefault' => ['bluesky_bydefault', DI::l10n()->t('Post to Bluesky by default'), $def_enabled],
		'$host'      => ['bluesky_host', DI::l10n()->t('Bluesky host'), $host, '', '', '', 'url'],
		'$handle'    => ['bluesky_handle', DI::l10n()->t('Bluesky handle'), $handle],
		'$did'       => ['bluesky_did', DI::l10n()->t('Bluesky DID'), $did, DI::l10n()->t('This is the unique identifier. It will be fetched automatically, when the handle is entered.'), '', 'readonly'],
		'$username'  => ['bluesky_username', DI::l10n()->t('Bluesky app username'), $username, DI::l10n()->t("Please don't add your real username here, but instead create a specific app username and app password in the Bluesky settings.")],
		'$password'  => ['bluesky_password', DI::l10n()->t('Bluesky app password'), ''],
	]);

	$data = [
		'connector' => 'bluesky',
		'title'     => DI::l10n()->t('Bluesky Export'),
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
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'username',        $_POST['bluesky_username']);

	if (!empty($_POST['bluesky_password'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'app_password', $_POST['bluesky_password']);
	}

	if (!empty($host) && !empty($handle)) {
		if (empty($old_did) || $old_host != $host || $old_handle != $handle) {
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'bluesky', 'did', bluesky_get_did(DI::userSession()->getLocalUserId()));
		}
	} else {
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'bluesky', 'did');
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

	if (!strstr($post['postopts'] ?? '', 'bluesky') || ($post['parent'] != $post['id']) || $post['private']) {
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
		return;
	} elseif ($b['private'] || !strstr($b['postopts'], 'bluesky')) {
		return;
	}

	bluesky_create_post($b);
}

function bluesky_create_post(array $item)
{
	$uid = $item['uid'];
	$token = bluesky_get_token($uid);
	if (empty($token)) {
		return;
	}

	$did  = DI::pConfig()->get($uid, 'bluesky', 'did');

	$msg = Plaintext::getPost($item, 300, false, BBCode::CONNECTORS);
	$parent = $root = [];
	foreach ($msg['parts'] as $key => $part) {
		$record = [
			'text'      => $part,
			'createdAt' => DateTimeFormat::utcNow(DateTimeFormat::ATOM),
			'$type'     => 'app.bsky.feed.post'
		];

		if (!empty($root)) {
			$record['reply'] = ['root' => $root, 'parent' => $parent];
		}

		if ($key == count($msg['parts']) - 1) {
			$record = bluesky_add_embed($uid, $msg, $record);
		}

		$post = [
			'collection' => 'app.bsky.feed.post',
			'repo'       => $did,
			'record'     => $record
		];

		$parent = bluesky_post($uid, '/xrpc/com.atproto.repo.createRecord', json_encode($post), ['Content-type' => 'application/json', 'Authorization' => ['Bearer ' . $token]]);
		if (empty($parent)) {
			return;
		}
		Logger::debug('Posting done', ['return' => $parent]);
		if (empty($root)) {
			$root = $parent;
		}
	}
}

function bluesky_add_embed(int $uid, array $msg, array $record): array
{
	if (($msg['type'] != 'link') && !empty($msg['images'])) {
		$images = [];
		foreach ($msg['images'] as $image) {
			$photo = Photo::selectFirst(['resource-id'], ['id' => $image['id']]);
			$photo = Photo::selectFirst([], ["`resource-id` = ? AND `scale` > ?", $photo['resource-id'], 0], ['order' => ['scale']]);
			$blob = bluesky_upload_blob($uid, $photo);
			if (!empty($blob)) {
				$images[] = ['alt' => $image['description'], 'image' => $blob];
			}
		}
		if (!empty($images)) {
			$record['embed'] = ['$type' => 'app.bsky.embed.images', 'images' => $images];
		}
	} elseif ($msg['type'] == 'link') {
		$record['embed'] = [
			'$type'    => 'app.bsky.embed.external',
			'external' => [
				'uri'         => $msg['url'],
				'title'       => $msg['title'],
				'description' => $msg['description'],
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
	$content = Photo::getImageForPhoto($photo);
	$data = bluesky_post($uid, '/xrpc/com.atproto.repo.uploadBlob', $content, ['Content-type' => $photo['type'], 'Authorization' => ['Bearer ' . bluesky_get_token($uid)]]);
	if (empty($data)) {
		return null;
	}

	Logger::debug('Uploaded blob', ['return' => $data]);
	return $data->blob;
}

function bluesky_get_timeline(int $uid)
{
	$data = bluesky_get($uid, '/xrpc/app.bsky.feed.getTimeline', HttpClientAccept::JSON, [HttpClientOptions::HEADERS => ['Authorization' => ['Bearer ' . bluesky_get_token($uid)]]]);
	if (empty($data)) {
		return;
	}
	// TODO Add Functionality to read the timeline
}

function bluesky_get_did(int $uid): string
{
	$data = bluesky_get($uid, '/xrpc/com.atproto.identity.resolveHandle?handle=' . DI::pConfig()->get($uid, 'bluesky', 'handle'));
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
		return bluesky_create_token($uid);
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

function bluesky_create_token(int $uid): string
{
	$did      = DI::pConfig()->get($uid, 'bluesky', 'did');
	$password = DI::pConfig()->get($uid, 'bluesky', 'app_password');

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
