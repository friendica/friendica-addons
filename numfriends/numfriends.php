<?php
/**
 * Name: Numfriends
 * Description: Change number of contacts shown of profile sidebar
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;

function numfriends_install() {

	Hook::register('addon_settings', 'addon/numfriends/numfriends.php', 'numfriends_settings');
	Hook::register('addon_settings_post', 'addon/numfriends/numfriends.php', 'numfriends_settings_post');

	Logger::log("installed numfriends");
}


function numfriends_uninstall() {

	Hook::unregister('addon_settings', 'addon/numfriends/numfriends.php', 'numfriends_settings');
	Hook::unregister('addon_settings_post', 'addon/numfriends/numfriends.php', 'numfriends_settings_post');


	Logger::log("removed numfriends");
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
	if(! local_user() || empty($_POST['numfriends-submit']))
		return;

	PConfig::set(local_user(),'system','display_friend_count',intval($_POST['numfriends']));
	info( L10n::t('Numfriends settings updated.') . EOL);
}


/**
 *
 * Called from the Addon Setting form. 
 * Add our own settings info to the page.
 *
 */
function numfriends_settings(&$a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/numfriends/numfriends.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$numfriends = PConfig::get(local_user(), 'system', 'display_friend_count', 24);
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . L10n::t('Numfriends Settings') . '</h3>';
	$s .= '<div id="numfriends-wrapper">';
	$s .= '<label id="numfriends-label" for="numfriends">' . L10n::t('How many contacts to display on profile sidebar') . '</label>';
	$s .= '<input id="numfriends-input" type="text" name="numfriends" value="' . intval($numfriends) . '" ' . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="numfriends-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';
}
