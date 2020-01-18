<?php
/**
 * Name: Group Text
 * Description: Disable images in group edit menu
 * Version: 1.0
 * Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\DI;

function group_text_install() {

	Hook::register('addon_settings', 'addon/group_text/group_text.php', 'group_text_settings');
	Hook::register('addon_settings_post', 'addon/group_text/group_text.php', 'group_text_settings_post');

	Logger::log("installed group_text");
}


function group_text_uninstall() {

	Hook::unregister('addon_settings', 'addon/group_text/group_text.php', 'group_text_settings');
	Hook::unregister('addon_settings_post', 'addon/group_text/group_text.php', 'group_text_settings_post');


	Logger::log("removed group_text");
}



/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function group_text_settings_post($a,$post) {
	if(! local_user() || empty($_POST['group_text-submit']))
		return;
	DI::pConfig()->set(local_user(),'system','groupedit_image_limit',intval($_POST['group_text']));

	info(L10n::t('Group Text settings updated.') . EOL);
}


/**
 *
 * Called from the Addon Setting form. 
 * Add our own settings info to the page.
 *
 */



function group_text_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/group_text/group_text.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = DI::pConfig()->get(local_user(),'system','groupedit_image_limit');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . L10n::t('Group Text') . '</h3>';
	$s .= '<div id="group_text-enable-wrapper">';
	$s .= '<label id="group_text-enable-label" for="group_text-checkbox">' . L10n::t('Use a text only (non-image) group selector in the "group edit" menu') . '</label>';
	$s .= '<input id="group_text-checkbox" type="checkbox" name="group_text" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="group_text-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

}
