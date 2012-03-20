<?php
/**
 * Name: Frown
 * Description: Disable graphical smilies
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 *
 */


function frown_install() {

	register_hook('plugin_settings', 'addon/frown/frown.php', 'frown_settings');
	register_hook('plugin_settings_post', 'addon/frown/frown.php', 'frown_settings_post');

	logger("installed frown");
}


function frown_uninstall() {

	unregister_hook('plugin_settings', 'addon/frown/frown.php', 'frown_settings');
	unregister_hook('plugin_settings_post', 'addon/frown/frown.php', 'frown_settings_post');


	logger("removed frown");
}



/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function frown_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'frown-submit')))
		return;
	set_pconfig(local_user(),'system','no_smilies',intval($_POST['frown']));

	info( t('Frown settings updated.') . EOL);
}


/**
 *
 * Called from the Plugin Setting form. 
 * Add our own settings info to the page.
 *
 */



function frown_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/frown/frown.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = get_pconfig(local_user(),'system','no_smilies');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Frown Settings') . '</h3>';
	$s .= '<div id="frown-enable-wrapper">';
	$s .= '<label id="frown-enable-label" for="frown-checkbox">' . t('Disable graphical smilies') . '</label>';
	$s .= '<input id="frown-checkbox" type="checkbox" name="frown" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="frown-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}
