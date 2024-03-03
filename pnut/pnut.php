<?php
/**
 * Name: Pnut Connector
 * Description: Post to pnut.io
 * Version: 0.1.2
 * Author: Morgan McMillian <https://social.clacks.network/profile/spacenerdmo>
 * Status: In Development
 */

require_once 'addon/pnut/lib/phpnut.php';
require_once 'addon/pnut/lib/phpnutException.php';

use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\DI;
use Friendica\Model\Photo;
use phpnut\phpnutException;

const PNUT_LIMIT = 256;

function pnut_install()
{
	Hook::register('load_config',             __FILE__, 'pnut_load_config');
	Hook::register('hook_fork',               __FILE__, 'pnut_hook_fork');
	Hook::register('post_local',              __FILE__, 'pnut_post_local');
	Hook::register('notifier_normal',         __FILE__, 'pnut_post_hook');
	Hook::register('jot_networks',            __FILE__, 'pnut_jot_nets');
	Hook::register('connector_settings',      __FILE__, 'pnut_settings');
	Hook::register('connector_settings_post', __FILE__, 'pnut_settings_post');
}

function pnut_module() {}

function pnut_content()
{
	if (!DI::userSession()->getLocalUserId()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Permission denied.'));
		return '';
	}

	if (isset(DI::args()->getArgv()[1])) {
		switch (DI::args()->getArgv()[1]) {
			case 'connect':
				$o = pnut_connect();
				break;

			default:
				$o = print_r(DI::args()->getArgv(), true);
				break;
		}
	} else {
		$o = pnut_connect();
	}
	return $o;
}

function pnut_connect()
{
	$client_id     = DI::config()->get('pnut', 'client_id');
	$client_secret = DI::config()->get('pnut', 'client_secret');

	if (empty($client_id) || empty($client_secret)) {
		$client_id     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'client_id');
		$client_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'client_secret');
	}

	$callback_url = DI::baseUrl() . '/pnut/connect';

	$nut = new phpnut\phpnut($client_id, $client_secret);

	try {
		$token = $nut->getAccessToken($callback_url);
		Logger::debug('Got Token', [$token]);
		$o = DI::l10n()->t('You are now authenticated with pnut.io.');
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pnut', 'access_token', $token);
	} catch (phpnutException $e) {
		$o = DI::l10n()->t('Error fetching token. Please try again.', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
	}

	$o .= '<br /><a href="' . DI::baseUrl() . '/settings/connectors">' . DI::l10n()->t("return to the connector page").'</a>';

	return $o;
}

function pnut_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('pnut'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function pnut_addon_admin(string &$o)
{
	$client_id     = DI::config()->get('pnut', 'client_id');
	$client_secret = DI::config()->get('pnut', 'client_secret');

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/pnut/');

	$o = Renderer::replaceMacros($t, [
		'$submit'        => DI::l10n()->t('Save Settings'),
		'$client_id'     => ['pnut_client_id', DI::l10n()->t('Client ID'), $client_id],
		'$client_secret' => ['pnut_client_secret', DI::l10n()->t('Client Secret'), $client_secret],
	]);
}

function pnut_addon_admin_post()
{
	DI::config()->set('pnut', 'client_id',     $_POST['pnut_client_id']);
	DI::config()->set('pnut', 'client_secret', $_POST['pnut_client_secret']);
}

function pnut_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$redirectUri  = DI::baseUrl() . '/pnut/connect';
	$scope        = ['write_post','files'];

	$enabled       = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'post') ?? false;
	$def_enabled   = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'post_by_default') ?? false;
	$client_id     = DI::config()->get('pnut', 'client_id');
	$client_secret = DI::config()->get('pnut', 'client_secret');
	$token         = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'access_token');
	
	$user_client = empty($client_id) || empty($client_secret);
	if ($user_client) {
		$client_id     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'client_id');
		$client_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'client_secret');
	}

	if (!empty($client_id) && !empty($client_secret) && empty($token)) {
		$nut = new phpnut\phpnut($client_id, $client_secret);
		$authorize_url = $nut->getAuthUrl($redirectUri, $scope);
		$authorize_text = DI::l10n()->t('Authenticate with pnut.io');
	}

	if (!empty($token)) {
		$disconn_btn = DI::l10n()->t('Disconnect');
	}

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/pnut/');
	$html = Renderer::replaceMacros($t, [
		'$enable'         => ['pnut', DI::l10n()->t('Enable Pnut Post Addon'), $enabled],
		'$bydefault'      => ['pnut_bydefault', DI::l10n()->t('Post to Pnut by default'), $def_enabled],
		'$client_id'      => ['pnut_client_id', DI::l10n()->t('Client ID'), $client_id],
		'$client_secret'  => ['pnut_client_secret', DI::l10n()->t('Client Secret'), $client_secret],
		'$access_token'   => ['pnut_access_token', DI::l10n()->t('Access Token'), $token, '', '', 'readonly'],
		'$authorize_url'  => $authorize_url ?? '',
		'$authorize_text' => $authorize_text ?? '',
		'$disconn_btn'    => $disconn_btn ?? '',
		'user_client'     => $user_client,
	]);

	$data = [
		'connector' => 'pnut',
		'title'     => DI::l10n()->t('Pnut Export'),
		'image'     => 'addon/pnut/pnut.svg',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}

function pnut_settings_post(array &$b)
{
	if (empty($_POST['pnut-submit']) && empty($_POST['pnut-disconnect'])) {
		return;
	}

	if (!empty($_POST['pnut-disconnect'])) {
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'pnut', 'post');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'pnut', 'post_by_default');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'pnut', 'client_id');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'pnut', 'client_secret');
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'pnut', 'access_token');
	} else {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pnut', 'post',            intval($_POST['pnut']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pnut', 'post_by_default', intval($_POST['pnut_bydefault']));
		if (!empty($_POST['pnut_client_id'])) {
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pnut', 'client_id',     $_POST['pnut_client_id']);
		}
		if (!empty($_POST['pnut_client_secret'])) {
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pnut', 'client_secret', $_POST['pnut_client_secret']);
		}
	}
}

function pnut_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'post')) {
		$jotnets_fields[] = [
			'type'  => 'checkbox',
			'field' => [
				'pnut_enable',
				DI::l10n()->t('Post to Pnut'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'post_by_default')
			]
		];
	}
}

function pnut_hook_fork(array &$b)
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

	if (!strstr($post['postopts'] ?? '', 'pnut') || ($post['parent'] != $post['id']) || $post['private']) {
		$b['execute'] = false;
		return;
	}
}

function pnut_post_local(array &$b)
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

	$pnut_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'post'));
	$pnut_enable = (($pnut_post && !empty($_REQUEST['pnut_enable'])) ? intval($_REQUEST['pnut_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pnut', 'post_by_default'))) {
		$pnut_enable = 1;
	}

	if (!$pnut_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'pnut';
}

function pnut_post_hook(array &$b)
{
	/**
	 * Post to pnut.io
	 */
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	Logger::notice('PNUT post invoked', ['id' => $b['id'], 'guid' => $b['guid'], 'plink' => $b['plink']]);
	Logger::debug('PNUT array', $b);

	$token = DI::pConfig()->get($b['uid'], 'pnut', 'access_token');
	$nut = new phpnut\phpnut($token);

	$msgarr = Plaintext::getPost($b, PNUT_LIMIT, true, BBCode::EXTERNAL);
	$text = $msgarr['text'];
	$raw = [];

	Logger::debug('PNUT msgarr', $msgarr);

	if (count($msgarr['parts']) > 1) {
		$tstamp = time();
		$raw['nl.chimpnut.blog.post'][] = ['body' => $b['body'], 'tstamp' => $tstamp];
		$text = Plaintext::shorten($text, 252 - strlen($b['plink']), $b['uid']);
		$text .= "\n" . $b['plink'];
	}

	if ($msgarr['type'] == 'link') {
		$text .= "\n[" . $msgarr['title'] . "](" . $msgarr['url'] . ")";
	}

	if ($msgarr['type'] == 'photo') {
		$fileraw = ['type' => 'dev.mcmillian.friendica.image', 'kind' => 'image', 'is_public' => true];
		foreach ($msgarr['images'] as $image) {
			Logger::debug('PNUT image', $image);

			if (!empty($image['id'])) {
				$photo = Photo::selectFirst([], ['id' => $image['id']]);
				Logger::debug('PNUT selectFirst');
			} else {
				$photo = Photo::createPhotoForExternalResource($image['url']);
				Logger::debug('PNUT createPhotoForExternalResource');
			}
			$picturedata = Photo::getImageForPhoto($photo);

			Logger::debug('PNUT photo', $photo);
			$picurefile = tempnam(System::getTempPath(), 'pnut');
			file_put_contents($picurefile, $picturedata);
			Logger::debug('PNUT got file?', ['filename' => $picurefile]);
			$imagefile = $nut->createFile($picurefile, $fileraw);
			Logger::debug('PNUT file', ['pnutimagefile' => $imagefile]);
			unlink($picurefile);

			$raw['io.pnut.core.oembed'][] = ['+io.pnut.core.file' => ['file_id' => $imagefile['id'], 'file_token' => $imagefile['file_token'], 'format' => 'oembed']];
		}
	}

	$raw['io.pnut.core.crosspost'][] = ['canonical_url' => $b['plink']];
	$nut->createPost($text, ['raw' => $raw]);

	Logger::debug('PNUT post complete', ['id' => $b['id'], 'text' => $text, 'raw' => $raw]);
}
