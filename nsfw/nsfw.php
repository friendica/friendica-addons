<?php


/**
 * Name: NSFW
 * Description: Collapse posts with inappropriate content
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 */

function nsfw_install() {
	register_hook('prepare_body', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body');
	register_hook('plugin_settings', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings');
	register_hook('plugin_settings_post', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings_post');

}


function nsfw_uninstall() {
	unregister_hook('prepare_body', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body');
	unregister_hook('plugin_settings', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings_post');

}





function nsfw_addon_settings(&$a,&$s) {


	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/nsfw/nsfw.css' . '" media="all" />' . "\r\n";


	$words = get_pconfig(local_user(),'nsfw','words');
	if(! $words)
		$words = 'nsfw,';

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('"Not Safe For Work" Settings') . '</h3>';
    $s .= '<div id="nsfw-wrapper">';
    $s .= '<label id="nsfw-label" for="nsfw-words">' . t('Comma separated words to treat as NSFW') . ' </label>';
    $s .= '<input id="nsfw-words" type="text" name="nsfw-words" value="' . $words .'" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="nsfw-submit" name="nsfw-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

	return;

}

function nsfw_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['nsfw-submit']) {
		set_pconfig(local_user(),'nsfw','words',trim($_POST['nsfw-words']));
		info( t('NSFW Settings saved.') . EOL);
	}
}

function nsfw_prepare_body(&$a,&$b) {

	$words = null;
	if(local_user()) {
		$words = get_pconfig(local_user(),'nsfw','words');
	}
	if($words) {
		$arr = explode(',',$words);
	}
	else {
		$arr = array('nsfw');
	}

	$found = false;
	if(count($arr)) {
		foreach($arr as $word) {
			if(! strlen(trim($word))) {
				continue;
			}

			if(stristr($b,$word)) {
				$found = true;
				break;
			}
		}
	}
	if($found) {
		$rnd = random_string(8);
		$b = '<div id="nsfw-wrap-' . $rnd . '" class="fakelink" onclick=openClose(\'nsfw-' . $rnd . '\'); >' . t('NSFW - Click to open/close') . '</div><div id="nsfw-' . $rnd . '" style="display: none; " >' . $b . '</div>';  
	}
}
