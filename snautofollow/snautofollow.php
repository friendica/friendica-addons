<?php
/**
 * Name: Snautofollow
 * Description: Automatic follow/friend of statusnet people who mention/follow you
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 *
 */


function snautofollow_install() {

	register_hook('connector_settings', 'addon/snautofollow/snautofollow.php', 'snautofollow_settings');
	register_hook('connector_settings_post', 'addon/snautofollow/snautofollow.php', 'snautofollow_settings_post');

}


function snautofollow_uninstall() {
	unregister_hook('connector_settings', 'addon/snautofollow/snautofollow.php', 'snautofollow_settings');
	unregister_hook('connector_settings_post', 'addon/snautofollow/snautofollow.php', 'snautofollow_settings_post');

}


function snautofollow_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'snautofollow-submit')))
		return;

	set_pconfig(local_user(),'system','ostatus_autofriend',intval($_POST['snautofollow']));
	info( t('StatusNet AutoFollow settings updated.') . EOL);
}

function snautofollow_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/snautofollow/snautofollow.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$snautofollow = get_pconfig(local_user(),'system','ostatus_autofriend');
	if($snautofollow === false)
		$snautofollow = false;

	$snautofollow_checked = (($snautofollow) ? ' checked="checked" ' : '');

	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('StatusNet AutoFollow Settings') . '</h3>';
	$s .= '<div id="snautofollow-wrapper">';
	$s .= '<label id="snautofollow-label" for="snautofollow-checkbox">' . t('Automatically follow any StatusNet followers/mentioners') . '</label>';
	$s .= '<input id="snautofollow-checkbox" type="checkbox" name="snautofollow" value="1" ' . $snautofollow_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="snautofollow-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}
