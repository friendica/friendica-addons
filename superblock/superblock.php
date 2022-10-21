<?php
/**
 * Name: superblock
 * Description: block people
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\Strings;

function superblock_install()
{
	Hook::register('addon_settings', 'addon/superblock/superblock.php', 'superblock_addon_settings');
	Hook::register('addon_settings_post', 'addon/superblock/superblock.php', 'superblock_addon_settings_post');
	Hook::register('conversation_start', 'addon/superblock/superblock.php', 'superblock_conversation_start');
	Hook::register('item_photo_menu', 'addon/superblock/superblock.php', 'superblock_item_photo_menu');
	Hook::register('enotify_store', 'addon/superblock/superblock.php', 'superblock_enotify_store');
}

function superblock_addon_settings(App &$a, array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$blocked = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'system', 'blocked', '');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/superblock/');
	$html = Renderer::replaceMacros($t, [
		'$urls' => ['superblock-words', DI::l10n()->t('Comma separated profile URLs to block'), $blocked],
	]);

	$data = [
		'addon' => 'superblock',
		'title' => DI::l10n()->t('Superblock'),
		'html'  => $html,
	];
}

function superblock_addon_settings_post(App $a, array &$b)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (!empty($_POST['superblock-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'system', 'blocked',trim($_POST['superblock-words']));
	}
}

function superblock_enotify_store(App $a, array &$b)
{
	if (empty($b['uid'])) {
		return;
	}

	$words = DI::pConfig()->get($b['uid'], 'system', 'blocked');
	if ($words) {
		$arr = explode(',', $words);
	} else {
		return;
	}

	$found = false;
	if (count($arr)) {
		foreach ($arr as $word) {
			if (!strlen(trim($word))) {
				continue;
			}

			if (Strings::compareLink($b['url'], $word)) {
				$found = true;
				break;
			}
		}
	}

	if ($found) {
		// Empty out the fields
		$b = [];
	}
}


function superblock_conversation_start(App $a, array &$b)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$words = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'system', 'blocked');
	if ($words) {
		$a->data['superblock'] = explode(',', $words);
	}

	DI::page()['htmlhead'] .= <<< EOT
<script>
function superblockBlock(author) {
	$.get('superblock?block=' +author, function(data) {
		location.reload(true);
	});
}
</script>
EOT;

}

function superblock_item_photo_menu(App $a, array &$b)
{
	if (!DI::userSession()->getLocalUserId() || $b['item']['self']) {
		return;
	}

	$blocked = false;
	$author = $b['item']['author-link'];
	if (!empty($a->data['superblock'])) {
		foreach ($a->data['superblock'] as $bloke) {
			if (Strings::compareLink($bloke, $author)) {
				$blocked = true;
				break;
			}
		}
	}

	$b['menu'][DI::l10n()->t('Block Completely')] = 'javascript:superblockBlock(\'' . $author . '\'); return false;';
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function superblock_module() {}

function superblock_init(App $a)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$words = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'system', 'blocked');

	if (array_key_exists('block', $_GET) && $_GET['block']) {
		if (strlen($words))
			$words .= ',';
		$words .= trim($_GET['block']);
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'system', 'blocked', $words);
	exit();
}
