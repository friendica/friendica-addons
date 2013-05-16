<?php


/**
 * Name: superblock
 * Description: block people
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 */

function superblock_install() {

	register_hook('plugin_settings', 'addon/superblock/superblock.php', 'superblock_addon_settings');
	register_hook('plugin_settings_post', 'addon/superblock/superblock.php', 'superblock_addon_settings_post');
	register_hook('conversation_start', 'addon/superblock/superblock.php', 'superblock_conversation_start');
	register_hook('item_photo_menu', 'addon/superblock/superblock.php', 'superblock_item_photo_menu');
	register_hook('enotify_store', 'addon/superblock/superblock.php', 'superblock_enotify_store');

}


function superblock_uninstall() {

	unregister_hook('plugin_settings', 'addon/superblock/superblock.php', 'superblock_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/superblock/superblock.php', 'superblock_addon_settings_post');
	unregister_hook('conversation_start', 'addon/superblock/superblock.php', 'superblock_conversation_start');
	unregister_hook('item_photo_menu', 'addon/superblock/superblock.php', 'superblock_item_photo_menu');
	unregister_hook('enotify_store', 'addon/superblock/superblock.php', 'superblock_enotify_store');

}





function superblock_addon_settings(&$a,&$s) {

	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/superblock/superblock.css' . '" media="all" />' . "\r\n";


	$words = get_pconfig(local_user(),'system','blocked');
	if(! $words)
		$words = '';

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('"Superblock" Settings') . '</h3>';
    $s .= '<div id="superblock-wrapper">';
    $s .= '<label id="superblock-label" for="superblock-words">' . t('Comma separated profile URLS to block') . ' </label>';
    $s .= '<textarea id="superblock-words" type="text" name="superblock-words" >' . htmlspecialchars($words) . '</textarea>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="superblock-submit" name="superblock-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

	return;

}

function superblock_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['superblock-submit']) {
		set_pconfig(local_user(),'system','blocked',trim($_POST['superblock-words']));
		info( t('SUPERBLOCK Settings saved.') . EOL);
	}
}

function superblock_enotify_store(&$a,&$b) {

	$words = get_pconfig($b['uid'],'system','blocked');
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

	$words = get_pconfig(local_user(),'system','blocked');
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

	$words = get_pconfig(local_user(),'system','blocked');

	if(array_key_exists('block',$_GET) && $_GET['block']) {
		if(strlen($words))
			$words .= ',';
		$words .= trim($_GET['block']);
	}

	set_pconfig(local_user(),'system','blocked',$words);
	info( t('superblock settings updated') . EOL );
	killme();
}
