<?php

/**
 * Name: IFTTT Receiver
 * Description: Receives a post from https://ifttt.com/ and distributes it.
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */
use Friendica\App;
use Friendica\Content\PageInfo;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Post;
use Friendica\Util\Strings;

function ifttt_install()
{
	Hook::register('connector_settings', 'addon/ifttt/ifttt.php', 'ifttt_settings');
	Hook::register('connector_settings_post', 'addon/ifttt/ifttt.php', 'ifttt_settings_post');
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function ifttt_module() {}

function ifttt_content() {}

function ifttt_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$key = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ifttt', 'key');
	if (!$key) {
		$key = Strings::getRandomHex(20);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ifttt', 'key', $key);
	}

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/ifttt/');
	$html = Renderer::replaceMacros($t, [
		'$l10n'                    => [
			'intro'                   => DI::l10n()->t('Create an account at <a href="http://www.ifttt.com">IFTTT</a>. Create three Facebook recipes that are connected with <a href="https://ifttt.com/maker">Maker</a> (In the form "if Facebook then Maker") with the following parameters:'),
			'url'                     => DI::l10n()->t('URL'),
			'method'                  => DI::l10n()->t('Method'),
			'content_type'            => DI::l10n()->t('Content Type'),
			'new_status_message_body' => DI::l10n()->t('Body for "new status message"'),
			'new_photo_upload_body'   => DI::l10n()->t('Body for "new photo upload"'),
			'new_link_post_body'      => DI::l10n()->t('Body for "new link post"'),
		],
		'$url'                     => DI::baseUrl() . '/ifttt/' . DI::userSession()->getLocalUserNickname(),
		'$new_status_message_body' => 'key=' . $key . '&type=status&msg=<<<{{Message}}>>>&date=<<<{{UpdatedAt}}>>>&url=<<<{{PageUrl}}>>>',
		'$new_photo_upload_body'   => 'key=' . $key . '&type=photo&link=<<<{{Link}}>>>&image=<<<{{ImageSource}}>>>&msg=<<<{{Caption}}>>>&date=<<<{{CreatedAt}}>>>&url=<<<{{PageUrl}}>>>',
		'$new_link_post_body'      => 'key=' . $key . '&type=link&link=<<<{{Link}}>>>&title=<<<{{Title}}>>>&msg=<<<{{Message}}>>>&description=<<<{{Description}}>>>&date=<<<{{CreatedAt}}>>>&url=<<<{{PageUrl}}>>>',
	]);

	$data = [
		'connector' => 'ifttt',
		'title'     => DI::l10n()->t('IFTTT Mirror'),
		'image'     => 'addon/ifttt/ifttt.png',
		'html'      => $html,
		'submit'    => DI::l10n()->t('Generate new key'),
	];
}

function ifttt_settings_post()
{
	if (!empty($_POST['ifttt-submit'])) {
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'ifttt', 'key');
	}
}

function ifttt_post()
{
	if (DI::args()->getArgc() != 2) {
		return;
	}

	$nickname = DI::args()->getArgv()[1];

	$user = DBA::selectFirst('user', ['uid'], ['nickname' => $nickname]);
	if (!DBA::isResult($user)) {
		Logger::info('User ' . $nickname . ' not found.');
		return;
	}

	$uid = $user['uid'];

	Logger::info('Received a post for user ' . $uid . ' from ifttt ' . print_r($_REQUEST, true));

	if (!isset($_REQUEST['key'])) {
		Logger::notice('No key found.');
		return;
	}

	$key = $_REQUEST['key'];

	// Check the key
	if ($key != DI::pConfig()->get($uid, 'ifttt', 'key')) {
		Logger::info('Invalid key for user ' . $uid);
		return;
	}

	$item = [];

	if (isset($_REQUEST['type'])) {
		$item['type'] = $_REQUEST['type'];
	}

	if (!in_array($item['type'], ['status', 'link', 'photo'])) {
		Logger::info('Unknown item type ' . $item['type']);
		return;
	}

	if (isset($_REQUEST['link'])) {
		$item['link'] = trim($_REQUEST['link']);
	}
	if (isset($_REQUEST['image'])) {
		$item['image'] = trim($_REQUEST['image']);
	}
	if (isset($_REQUEST['title'])) {
		$item['title'] = trim($_REQUEST['title']);
	}
	if (isset($_REQUEST['msg'])) {
		$item['msg'] = trim($_REQUEST['msg']);
	}
	if (isset($_REQUEST['description'])) {
		$item['description'] = trim($_REQUEST['description']);
	}
	if (isset($_REQUEST['date'])) {
		$item['date'] = date('c', strtotime($date = str_replace(' at ', ', ', $_REQUEST['date'])));
	}
	if (isset($_REQUEST['url'])) {
		$item['url'] = trim($_REQUEST['url']);
	}

	if ((substr($item['msg'], 0, 3) == '<<<') && (substr($item['msg'], -3, 3) == '>>>')) {
		$item['msg'] = substr($item['msg'], 3, -3);
	}

	ifttt_message($uid, $item);
}

function ifttt_message($uid, $item)
{
	$post = [];
	$post['uid'] = $uid;
	$post['app'] = 'IFTTT';
	$post['title'] = '';
	$post['body'] = $item['msg'];
	//$post['date'] = $item['date'];
	//$post['uri'] = $item['url'];

	if ($item['type'] == 'link') {
		$link = $item['link'];
		$data = PageInfo::queryUrl($item['link']);

		if (isset($item['title']) && (trim($item['title']) != '')) {
			$data['title'] = $item['title'];
		}

		if (isset($item['description']) && (trim($item['description']) != '')) {
			$data['text'] = $item['description'];
		}

		$post['body'] .= "\n" . PageInfo::getFooterFromData($data);
	} elseif (($item['type'] == 'photo') && ($item['image'] != '')) {
		$link = $item['image'];
		$post['body'] .= "\n\n[img]" . $item['image'] . "[/img]\n";
	} elseif (!empty($item['url'])) {
		$link = $item['url'];
	} else {
		$link = hash('ripemd128', $item['msg']);
	}

	Post\Delayed::add($link, $post, Worker::PRIORITY_MEDIUM, Post\Delayed::PREPARED);
}
