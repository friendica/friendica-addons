<?php


/**
 * Name: blockem
 * Description: block people
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 */

function blockem_install() {
	register_hook('prepare_body', 'addon/blockem/blockem.php', 'blockem_prepare_body');
	register_hook('plugin_settings', 'addon/blockem/blockem.php', 'blockem_addon_settings');
	register_hook('plugin_settings_post', 'addon/blockem/blockem.php', 'blockem_addon_settings_post');

}


function blockem_uninstall() {
	unregister_hook('prepare_body', 'addon/blockem/blockem.php', 'blockem_prepare_body');
	unregister_hook('plugin_settings', 'addon/blockem/blockem.php', 'blockem_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/blockem/blockem.php', 'blockem_addon_settings_post');

}





function blockem_addon_settings(&$a,&$s) {


	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/blockem/blockem.css' . '" media="all" />' . "\r\n";


	$words = get_pconfig(local_user(),'blockem','words');
	if(! $words)
		$words = '';

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('"Blockem" Settings') . '</h3>';
    $s .= '<div id="blockem-wrapper">';
    $s .= '<label id="blockem-label" for="blockem-words">' . t('Comma separated profile URLS to block') . ' </label>';
    $s .= '<input id="blockem-words" type="text" name="blockem-words" value="' . $words .'" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="blockem-submit" name="blockem-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

	return;

}

function blockem_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['blockem-submit']) {
		set_pconfig(local_user(),'blockem','words',trim($_POST['blockem-words']));
		info( t('BLOCKEM Settings saved.') . EOL);
	}
}

function blockem_prepare_body(&$a,&$b) {

	if(! local_user())
		return;

	$words = null;
	if(local_user()) {
		$words = get_pconfig(local_user(),'blockem','words');
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
		$b['item']['author-avatar'] = $a->get_baseurl() . "/images/default-profile-sm.jpg";
		$b['html'] = 
'<script>$("#wall-item-photo-' . $b['item']['id'] . '").removeAttr("src")</script>' . 
'<div id="blockem-wrap-' . $rnd . '" class="fakelink" onclick=openClose(\'blockem-' . $rnd . '\'); >' . sprintf( t('Blocked %s - Click to open/close'),$word ) . '</div><div id="blockem-' . $rnd . '" style="display: none; " >' . $b['html'] . '</div>';  
	}
}
