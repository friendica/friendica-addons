<?php


/**
 * Name: superblock
 * Description: block people
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\PConfig;

function superblock_install() {

	Addon::registerHook('addon_settings', 'addon/superblock/superblock.php', 'superblock_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/superblock/superblock.php', 'superblock_addon_settings_post');
	Addon::registerHook('conversation_start', 'addon/superblock/superblock.php', 'superblock_conversation_start');
	Addon::registerHook('item_photo_menu', 'addon/superblock/superblock.php', 'superblock_item_photo_menu');
	Addon::registerHook('enotify_store', 'addon/superblock/superblock.php', 'superblock_enotify_store');

}


function superblock_uninstall() {

	Addon::unregisterHook('addon_settings', 'addon/superblock/superblock.php', 'superblock_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/superblock/superblock.php', 'superblock_addon_settings_post');
	Addon::unregisterHook('conversation_start', 'addon/superblock/superblock.php', 'superblock_conversation_start');
	Addon::unregisterHook('item_photo_menu', 'addon/superblock/superblock.php', 'superblock_item_photo_menu');
	Addon::unregisterHook('enotify_store', 'addon/superblock/superblock.php', 'superblock_enotify_store');

}





function superblock_addon_settings(&$a,&$s) {

	if(! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/superblock/superblock.css' . '" media="all" />' . "\r\n";

	$words = PConfig::get(local_user(),'system','blocked');
	if(! $words) {
		$words = '';
	}

	$s .= '<span id="settings_superblock_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_superblock_expanded\'); openClose(\'settings_superblock_inflated\');">';
	$s .= '<h3>' . t('"Superblock"') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_superblock_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_superblock_expanded\'); openClose(\'settings_superblock_inflated\');">';
	$s .= '<h3>' . t('"Superblock"') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="superblock-wrapper">';
	$s .= '<label id="superblock-label" for="superblock-words">' . t('Comma separated profile URLS to block') . ' </label>';
	$s .= '<textarea id="superblock-words" type="text" name="superblock-words" >' . htmlspecialchars($words) . '</textarea>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="superblock-submit" name="superblock-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

	return;
}

function superblock_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['superblock-submit']) {
		PConfig::set(local_user(),'system','blocked',trim($_POST['superblock-words']));
		info( t('SUPERBLOCK Settings saved.') . EOL);
	}
}

function superblock_enotify_store(&$a,&$b) {

	$words = PConfig::get($b['uid'],'system','blocked');
	if($words) {
		$arr = explode(',',$words);
	}
	else {
		return;
	}

	$found = false;
	if(count($arr)) {
		foreach($arr as $word) {
			if(! strlen(trim($word))) {
				continue;
			}

			if(link_compare($b['url'],$word)) {
				$found = true;
				break;
			}
		}
	}
	if($found) {
		$b['abort'] = true;
	}
}


function superblock_conversation_start(&$a,&$b) {

	if(! local_user())
		return;

	$words = PConfig::get(local_user(),'system','blocked');
	if($words) {
		$a->data['superblock'] = explode(',',$words);
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

function superblock_item_photo_menu(&$a,&$b) {

	if((! local_user()) || ($b['item']['self']))
		return;

	$blocked = false;
	$author = $b['item']['author-link'];
	if(is_array($a->data['superblock'])) {
		foreach($a->data['superblock'] as $bloke) {
			if(link_compare($bloke,$author)) {
				$blocked = true;
				break;
			}
		}
	}

	$b['menu'][ t('Block Completely')] = 'javascript:superblockBlock(\'' . $author . '\'); return false;';
}

function superblock_module() {}


function superblock_init(&$a) {

	if(! local_user())
		return;

	$words = PConfig::get(local_user(),'system','blocked');

	if(array_key_exists('block',$_GET) && $_GET['block']) {
		if(strlen($words))
			$words .= ',';
		$words .= trim($_GET['block']);
	}

	PConfig::set(local_user(),'system','blocked',$words);
	info( t('superblock settings updated') . EOL );
	killme();
}
