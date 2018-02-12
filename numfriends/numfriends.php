<?php
/**
 * Name: Numfriends
 * Description: Change number of contacts shown of profile sidebar
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 *
 */


function numfriends_install() {

	register_hook('plugin_settings', 'addon/numfriends/numfriends.php', 'numfriends_settings');
	register_hook('plugin_settings_post', 'addon/numfriends/numfriends.php', 'numfriends_settings_post');

	logger("installed numfriends");
}


function numfriends_uninstall() {

	unregister_hook('plugin_settings', 'addon/numfriends/numfriends.php', 'numfriends_settings');
	unregister_hook('plugin_settings_post', 'addon/numfriends/numfriends.php', 'numfriends_settings_post');


	logger("removed numfriends");
}



/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function numfriends_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'numfriends-submit')))
		return;

	set_pconfig(local_user(),'system','display_friend_count',intval($_POST['numfriends']));
	info( t('Numfriends settings updated.') . EOL);
}


/**
 *
 * Called from the Plugin Setting form. 
 * Add our own settings info to the page.
 *
 */



function numfriends_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/numfriends/numfriends.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$numfriends = get_pconfig(local_user(),'system','display_friend_count');
	if($numfriends === false)
		$numfriends = 24;
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Numfriends Settings') . '</h3>';
	$s .= '<div id="numfriends-wrapper">';
	$s .= '<label id="numfriends-label" for="numfriends">' . t('How many contacts to display on profile sidebar') . '</label>';
	$s .= '<input id="numfriends-input" type="text" name="numfriends" value="' . intval($numfriends) . '" ' . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="numfriends-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}
