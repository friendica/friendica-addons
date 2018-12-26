<?php

/**
 * Name: IFTTT Receiver
 * Description: Receives a post from https://ifttt.com/ and distributes it.
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */
require_once 'mod/item.php';
use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Core\Protocol;
use Friendica\Database\DBA;
use Friendica\Model\Item;
use Friendica\Util\Strings;

function ifttt_install()
{
	Addon::registerHook('connector_settings', 'addon/ifttt/ifttt.php', 'ifttt_settings');
	Addon::registerHook('connector_settings_post', 'addon/ifttt/ifttt.php', 'ifttt_settings_post');
}

function ifttt_uninstall()
{
	Addon::unregisterHook('connector_settings', 'addon/ifttt/ifttt.php', 'ifttt_settings');
	Addon::unregisterHook('connector_settings_post', 'addon/ifttt/ifttt.php', 'ifttt_settings_post');
}

function ifttt_module()
{

}

function ifttt_content()
{

}

function ifttt_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$key = PConfig::get(local_user(), 'ifttt', 'key');

	if (!$key) {
		$key = Strings::getRandomHex(20);
		PConfig::set(local_user(), 'ifttt', 'key', $key);
	}

	$s .= '<span id="settings_ifttt_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_ifttt_expanded\'); openClose(\'settings_ifttt_inflated\');">';
	$s .= '<img class="connector" src="addon/ifttt/ifttt.png" /><h3 class="connector">' . L10n::t('IFTTT Mirror') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_ifttt_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_ifttt_expanded\'); openClose(\'settings_ifttt_inflated\');">';
	$s .= '<img class="connector" src="addon/ifttt/ifttt.png" /><h3 class="connector">' . L10n::t('IFTTT Mirror') . '</h3>';
	$s .= '</span>';

	$s .= '<div id="ifttt-configuration-wrapper">';
	$s .= '<p>' . L10n::t('Create an account at <a href="http://www.ifttt.com">IFTTT</a>. Create three Facebook recipes that are connected with <a href="https://ifttt.com/maker">Maker</a> (In the form "if Facebook then Maker") with the following parameters:') . '</p>';
	$s .= '<h4>URL</h4>';
	$s .= '<p>' . $a->getBaseURL() . '/ifttt/' . $a->user['nickname'] . '</p>';
	$s .= '<h4>Method</h4>';
	$s .= '<p>POST</p>';
	$s .= '<h4>Content Type</h4>';
	$s .= '<p>application/x-www-form-urlencoded</p>';
	$s .= '<h4>' . L10n::t('Body for "new status message"') . '</h4>';
	$s .= '<p><code>' . htmlentities('key=' . $key . '&type=status&msg=<<<{{Message}}>>>&date=<<<{{UpdatedAt}}>>>&url=<<<{{PageUrl}}>>>') . '</code></p>';
	$s .= '<h4>' . L10n::t('Body for "new photo upload"') . '</h4>';
	$s .= '<p><code>' . htmlentities('key=' . $key . '&type=photo&link=<<<{{Link}}>>>&image=<<<{{ImageSource}}>>>&msg=<<<{{Caption}}>>>&date=<<<{{CreatedAt}}>>>&url=<<<{{PageUrl}}>>>') . '</code></p>';
	$s .= '<h4>' . L10n::t('Body for "new link post"') . '</h4>';
	$s .= '<p><code>' . htmlentities('key=' . $key . '&type=link&link=<<<{{Link}}>>>&title=<<<{{Title}}>>>&msg=<<<{{Message}}>>>&description=<<<{{Description}}>>>&date=<<<{{CreatedAt}}>>>&url=<<<{{PageUrl}}>>>') . '</code></p>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="ifttt-rekey-wrapper">';
	$s .= '<label id="ifttt-rekey-label" for="ifttt-checkbox">' . L10n::t('Generate new key') . '</label>';
	$s .= '<input id="ifttt-checkbox" type="checkbox" name="ifttt-rekey" value="1" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="ifttt-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
	$s .= '</div>';
}

function ifttt_settings_post()
{
	if (!empty($_POST['ifttt-submit']) && isset($_POST['ifttt-rekey'])) {
		PConfig::delete(local_user(), 'ifttt', 'key');
	}
}

function ifttt_post(App $a)
{
	if ($a->argc != 2) {
		return;
	}

	$nickname = $a->argv[1];

	$user = DBA::selectFirst('user', ['uid'], ['nickname' => $nickname]);
	if (!DBA::isResult($user)) {
		Logger::log('User ' . $nickname . ' not found.', Logger::DEBUG);
		return;
	}

	$uid = $user['uid'];

	Logger::log('Received a post for user ' . $uid . ' from ifttt ' . print_r($_REQUEST, true), Logger::DEBUG);

	if (!isset($_REQUEST['key'])) {
		Logger::log('No key found.');
		return;
	}

	$key = $_REQUEST['key'];

	// Check the key
	if ($key != PConfig::get($uid, 'ifttt', 'key')) {
		Logger::log('Invalid key for user ' . $uid, Logger::DEBUG);
		return;
	}

	$item = [];

	if (isset($_REQUEST['type'])) {
		$item['type'] = $_REQUEST['type'];
	}

	if (!in_array($item['type'], ['status', 'link', 'photo'])) {
		Logger::log('Unknown item type ' . $item['type'], Logger::DEBUG);
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
	$a = get_app();

	$_SESSION['authenticated'] = true;
	$_SESSION['uid'] = $uid;

	unset($_REQUEST);
	$_REQUEST['api_source'] = true;
	$_REQUEST['profile_uid'] = $uid;
	$_REQUEST['source'] = 'IFTTT';
	$_REQUEST['title'] = '';
	$_REQUEST['body'] = $item['msg'];
	//$_REQUEST['date'] = $item['date'];
	//$_REQUEST['uri'] = $item['url'];

	if (!empty($item['url']) && strstr($item['url'], 'facebook.com')) {
		$hash = hash('ripemd128', $item['url']);
		$_REQUEST['extid'] = Protocol::FACEBOOK;
		$_REQUEST['message_id'] = Item::newURI($uid, Protocol::FACEBOOK . ':' . $hash);
	}

	if ($item['type'] == 'link') {
		$data = query_page_info($item['link']);

		if (isset($item['title']) && (trim($item['title']) != '')) {
			$data['title'] = $item['title'];
		}

		if (isset($item['description']) && (trim($item['description']) != '')) {
			$data['text'] = $item['description'];
		}

		$_REQUEST['body'] .= add_page_info_data($data);
	} elseif (($item['type'] == 'photo') && ($item['image'] != '')) {
		$_REQUEST['body'] .= "\n\n[img]" . $item['image'] . "[/img]\n";
	}

	item_post($a);
}
