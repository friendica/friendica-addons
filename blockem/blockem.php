<?php


/**
 * Name: blockem
 * Description: block people
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

use Friendica\Core\PConfig;

function blockem_install() {
	register_hook('prepare_body', 'addon/blockem/blockem.php', 'blockem_prepare_body');
	register_hook('display_item', 'addon/blockem/blockem.php', 'blockem_display_item');
	register_hook('plugin_settings', 'addon/blockem/blockem.php', 'blockem_addon_settings');
	register_hook('plugin_settings_post', 'addon/blockem/blockem.php', 'blockem_addon_settings_post');
	register_hook('conversation_start', 'addon/blockem/blockem.php', 'blockem_conversation_start');
	register_hook('item_photo_menu', 'addon/blockem/blockem.php', 'blockem_item_photo_menu');
	register_hook('enotify_store', 'addon/blockem/blockem.php', 'blockem_enotify_store' );
}


function blockem_uninstall() {
	unregister_hook('prepare_body', 'addon/blockem/blockem.php', 'blockem_prepare_body');
	unregister_hook('display_item', 'addon/blockem/blockem.php', 'blockem_display_item');
	unregister_hook('plugin_settings', 'addon/blockem/blockem.php', 'blockem_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/blockem/blockem.php', 'blockem_addon_settings_post');
	unregister_hook('conversation_start', 'addon/blockem/blockem.php', 'blockem_conversation_start');
	unregister_hook('item_photo_menu', 'addon/blockem/blockem.php', 'blockem_item_photo_menu');
	unregister_hook('enotify_store', 'addon/blockem/blockem.php', 'blockem_enotify_store' );

}





function blockem_addon_settings(&$a,&$s) {

	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/blockem/blockem.css' . '" media="all" />' . "\r\n";


	$words = PConfig::get(local_user(),'blockem','words');
	if(! $words)
		$words = '';

    $s .= '<span id="settings_blockem_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_blockem_expanded\'); openClose(\'settings_blockem_inflated\');">';
    $s .= '<h3>' . t('"Blockem"') . '</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_blockem_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_blockem_expanded\'); openClose(\'settings_blockem_inflated\');">';
    $s .= '<h3>' . t('"Blockem"') . '</h3>';
    $s .= '</span>';

    $s .= '<div id="blockem-wrapper">';
    $s .= '<label id="blockem-label" for="blockem-words">' . t('Comma separated profile URLS to block') . ' </label>';
    $s .= '<textarea id="blockem-words" type="text" name="blockem-words" >' . htmlspecialchars($words) . '</textarea>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="blockem-submit" name="blockem-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

	return;

}

function blockem_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['blockem-submit']) {
		PConfig::set(local_user(),'blockem','words',trim($_POST['blockem-words']));
		info( t('BLOCKEM Settings saved.') . EOL);
	}
}


function blockem_enotify_store(&$a,&$b) {

	$words = PConfig::get($b['uid'],'blockem','words');
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

function blockem_prepare_body(&$a,&$b) {

	if(! local_user())
		return;

	$words = null;
	if(local_user()) {
		$words = PConfig::get(local_user(),'blockem','words');
	}
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

			if(link_compare($b['item']['author-link'],$word)) {
				$found = true;
				break;
			}
		}
	}
	if($found) {
		$rnd = random_string(8);
		$b['html'] = '<div id="blockem-wrap-' . $rnd . '" class="fakelink" onclick=openClose(\'blockem-' . $rnd . '\'); >' . sprintf( t('Blocked %s - Click to open/close'),$word ) . '</div><div id="blockem-' . $rnd . '" style="display: none; " >' . $b['html'] . '</div>';
	}
}


function blockem_display_item(&$a,&$b) {
	if(strstr($b['output']['body'],'id="blockem-wrap-'))
		$b['output']['thumb'] = $a->get_baseurl() . "/images/person-80.jpg";
}


function blockem_conversation_start(&$a,&$b) {

	if(! local_user())
		return;

	$words = PConfig::get(local_user(),'blockem','words');
	if($words) {
		$a->data['blockem'] = explode(',',$words);
	}
	$a->page['htmlhead'] .= <<< EOT

<script>
function blockemBlock(author) {
	$.get('blockem?block=' +author, function(data) {
		location.reload(true);
	});
}
function blockemUnblock(author) {
	$.get('blockem?unblock=' +author, function(data) {
		location.reload(true);
	});
}
</script>

EOT;

}

function blockem_item_photo_menu(&$a,&$b) {

	if((! local_user()) || ($b['item']['self']))
		return;

	$blocked = false;
	$author = $b['item']['author-link'];
	if(is_array($a->data['blockem'])) {
		foreach($a->data['blockem'] as $bloke) {
			if(link_compare($bloke,$author)) {
				$blocked = true;
				break;
			}
		}
	}
	if($blocked)
		$b['menu'][ t('Unblock Author')] = 'javascript:blockemUnblock(\'' . $author . '\');';
	else
		$b['menu'][ t('Block Author')] = 'javascript:blockemBlock(\'' . $author . '\');';
}

function blockem_module() {}


function blockem_init(&$a) {

	if(! local_user())
		return;

	$words = PConfig::get(local_user(),'blockem','words');

	if(array_key_exists('block',$_GET) && $_GET['block']) {
		if(strlen($words))
			$words .= ',';
		$words .= trim($_GET['block']);
	}
	if(array_key_exists('unblock',$_GET) && $_GET['unblock']) {
		$arr = explode(',',$words);
		$newarr = [];

		if(count($arr)) {
			foreach($arr as $x) {
				if(! link_compare(trim($x),trim($_GET['unblock'])))
					$newarr[] = $x;
			}
		}
		$words = implode(',',$newarr);
	}

	PConfig::set(local_user(),'blockem','words',$words);
	info( t('blockem settings updated') . EOL );
	killme();
}
