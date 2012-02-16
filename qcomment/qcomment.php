<?php


/**
 * Name: Quick Comment
 * Description: Two click comments
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 */

function qcomment_install() {
	register_hook('plugin_settings', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings');
	register_hook('plugin_settings_post', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings_post');

}


function qcomment_uninstall() {
	unregister_hook('plugin_settings', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings_post');

}





function qcomment_addon_settings(&$a,&$s) {

	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/qcomment/qcomment.css' . '" media="all" />' . "\r\n";

	$words = get_pconfig(local_user(),'qcomment','words');
	if($words === false)
		$words = t(':-)') . "\n" . t(':-(') . "\n" .  t('lol');

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Quick Comment Settings') . '</h3>';
    $s .= '<div id="qcomment-wrapper">';
    $s .= '<label id="qcomment-label" for="qcomment-words">' . t('Enter quick comments, one per line') . ' </label>';
    $s .= '<textarea id="qcomment-words" type="text" name="qcomment-words" >' . htmlspecialchars(unxmlify($words)) . '</textarea>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="qcomment-submit" name="qcomment-submit" class="settings-submit" value="' . t('Submit') . '" /></div>';
	$s .= '</div>';

	return;

}

function qcomment_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['qcomment-submit']) {
		set_pconfig(local_user(),'qcomment','words',xmlify($_POST['qcomment-words']));
		info( t('Quick Comment settings saved.') . EOL);
	}
}

