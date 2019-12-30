<?php
/**
 * Name: superblock
 * Description: block people
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;
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

function superblock_uninstall()
{
	Hook::unregister('addon_settings', 'addon/superblock/superblock.php', 'superblock_addon_settings');
	Hook::unregister('addon_settings_post', 'addon/superblock/superblock.php', 'superblock_addon_settings_post');
	Hook::unregister('conversation_start', 'addon/superblock/superblock.php', 'superblock_conversation_start');
	Hook::unregister('item_photo_menu', 'addon/superblock/superblock.php', 'superblock_item_photo_menu');
	Hook::unregister('enotify_store', 'addon/superblock/superblock.php', 'superblock_enotify_store');
}

function superblock_addon_settings(&$a, &$s)
{
	if (!local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/superblock/superblock.css' . '" media="all" />' . "\r\n";

	$words = PConfig::get(local_user(), 'system', 'blocked');
	if (!$words) {
		$words = '';
	}

	$s .= '<span id="settings_superblock_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_superblock_expanded\'); openClose(\'settings_superblock_inflated\');">';
	$s .= '<h3>' . L10n::t('Superblock') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_superblock_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_superblock_expanded\'); openClose(\'settings_superblock_inflated\');">';
	$s .= '<h3>' . L10n::t('Superblock') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="superblock-wrapper">';
	$s .= '<label id="superblock-label" for="superblock-words">' . L10n::t('Comma separated profile URLS to block') . ' </label>';
	$s .= '<textarea id="superblock-words" type="text" name="superblock-words" >' . htmlspecialchars($words) . '</textarea>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="superblock-submit" name="superblock-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

	return;
}

function superblock_addon_settings_post(&$a, &$b)
{
	if (!local_user()) {
		return;
	}

	if (!empty($_POST['superblock-submit'])) {
		PConfig::set(local_user(), 'system', 'blocked',trim($_POST['superblock-words']));
		info(L10n::t('SUPERBLOCK Settings saved.') . EOL);
	}
}

function superblock_enotify_store(&$a,&$b) {

	$words = PConfig::get($b['uid'], 'system', 'blocked');
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
		$b['abort'] = true;
	}
}


function superblock_conversation_start(&$a, &$b)
{
	if (!local_user()) {
		return;
	}

	$words = PConfig::get(local_user(), 'system', 'blocked');
	if ($words) {
		$a->data['superblock'] = explode(',', $words);
	}
	$a->page['htmlhead'] .= <<< EOT

<script>
function superblockBlock(author) {
	$.get('superblock?block=' +author, function(data) {
		location.reload(true);
	});
}
</script>

EOT;

}

function superblock_item_photo_menu(&$a, &$b)
{
	if (!local_user() || $b['item']['self']) {
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

	$b['menu'][L10n::t('Block Completely')] = 'javascript:superblockBlock(\'' . $author . '\'); return false;';
}

function superblock_module() {}


function superblock_init(&$a)
{
	if (!local_user()) {
		return;
	}

	$words = PConfig::get(local_user(), 'system', 'blocked');

	if (array_key_exists('block', $_GET) && $_GET['block']) {
		if (strlen($words))
			$words .= ',';
		$words .= trim($_GET['block']);
	}

	PConfig::set(local_user(), 'system', 'blocked', $words);
	info(L10n::t('superblock settings updated') . EOL );
	exit();
}
