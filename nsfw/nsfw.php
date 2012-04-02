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

	$enable_checked = (intval(get_pconfig(local_user(),'nsfw','disable')) ? '' : ' checked="checked" ');
	$words = get_pconfig(local_user(),'nsfw','words');
	if(! $words)
		$words = 'nsfw,';

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Content Filter Settings') . '</h3>';
    $s .= '<div id="nsfw-wrapper">';
	
    $s .= '<label id="nsfw-enable-label" for="nsfw-enable">' . t('Enable Content filter') . ' </label>';
    $s .= '<input id="nsfw-enable" type="checkbox" name="nsfw-enable" value="1"' . $enable_checked . ' />';
	$s .= '<div class="clear"></div>';
    $s .= '<label id="nsfw-label" for="nsfw-words">' . t('Comma separated list of keywords to hide') . ' </label>';
    $s .= '<input id="nsfw-words" type="text" name="nsfw-words" value="' . $words .'" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="nsfw-submit" name="nsfw-submit" class="settings-submit" value="' . t('Submit') . '" /></div>';
	$s .= '<div class="nsfw-desc">' . t('Use /expression/ to provide regular expressions') . '</div></div>';

	return;

}

function nsfw_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['nsfw-submit']) {
		set_pconfig(local_user(),'nsfw','words',trim($_POST['nsfw-words']));
		$enable = ((x($_POST,'nsfw-enable')) ? intval($_POST['nsfw-enable']) : 0);
		$disable = 1-$enable;
		set_pconfig(local_user(),'nsfw','disable', $disable);
		info( t('NSFW Settings saved.') . EOL);
	}
}

function nsfw_prepare_body(&$a,&$b) {

	$words = null;
	if(get_pconfig(local_user(),'nsfw','disable'))
		return;

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
			if(strpos($word,'/') === 0) {
				if(preg_match($word,$b['html'])) {
					$found = true;
					break;
				}
			}
			else {
				if(stristr($b['html'],$word)) {
					$found = true;
					break;
				}
				if(stristr($b['item']['tag'], ']' . $word . '[' )) {
					$found = true;
					break;
				}
			} 
		}
	}
	if($found) {
		$rnd = random_string(8);
		$b['html'] = '<div id="nsfw-wrap-' . $rnd . '" class="fakelink" onclick=openClose(\'nsfw-' . $rnd . '\'); >' . sprintf( t('%s - Click to open/close'),$word ) . '</div><div id="nsfw-' . $rnd . '" style="display: none; " >' . $b['html'] . '</div>';  
	}
}
