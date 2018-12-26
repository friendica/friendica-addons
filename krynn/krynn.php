<?php
/**
 * Name: Dragonlance Krynn locales
 * Description: Set a random locale from the Dragonlance Realm of Krynn when posting. Based on the planets frindica addon by Mike Macgirvin and Tony Baldwin
 * Version: 1.0
 * Planets Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Planets Author: Tony Baldwin <https://free-haven.org/profile/tony>
 * Author: Dylan Thiedeke <https://theronin.net/profile/swathe>
 *
 *"My body was my sacrifice... for my magic. This damage is permanent." - Raistlin Majere
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;

function krynn_install() {

	/**
	 *
	 * Our demo addon will attach in three places.
	 * The first is just prior to storing a local post.
	 *
	 */

	Hook::register('post_local', 'addon/krynn/krynn.php', 'krynn_post_hook');

	/**
	 *
	 * Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences.
	 *
	 */

	Hook::register('addon_settings', 'addon/krynn/krynn.php', 'krynn_settings');
	Hook::register('addon_settings_post', 'addon/krynn/krynn.php', 'krynn_settings_post');

	Logger::log("installed krynn");
}


function krynn_uninstall() {

	/**
	 *
	 * uninstall unregisters any hooks created with register_hook
	 * during install. It may also delete configuration settings
	 * and any other cleanup.
	 *
	 */

	Hook::unregister('post_local',    'addon/krynn/krynn.php', 'krynn_post_hook');
	Hook::unregister('addon_settings', 'addon/krynn/krynn.php', 'krynn_settings');
	Hook::unregister('addon_settings_post', 'addon/krynn/krynn.php', 'krynn_settings_post');


	Logger::log("removed krynn");
}



function krynn_post_hook($a, &$item) {

	/**
	 *
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our addon
	 *
	 */

	Logger::log('krynn invoked');

	if(! local_user())   /* non-zero if this is a logged in user of this system */
		return;

	if(local_user() != $item['uid'])    /* Does this person own the post? */
		return;

	if($item['parent'])   /* If the item has a parent, this is a comment or something else, not a status post. */
		return;

	/* Retrieve our personal config setting */

	$active = PConfig::get(local_user(), 'krynn', 'enable');

	if(! $active)
		return;

	/**
	 *
	 * OK, we're allowed to do our stuff.
	 * Here's what we are going to do:
	 * load the list of timezone names, and use that to generate a list of krynn locales.
	 * Then we'll pick one of those at random and put it in the "location" field for the post.
	 *
	 */

	$krynn = ['Ansalon','Abanasinia','Solace','Haven','Gateway','Qualinost','Ankatavaka','Pax Tharkas','Ergoth','Newsea','Straights of Schallsea','Plains of Dust','Tarsis','Barren Hills','Que Shu','Citadel of Light','Solinari','Hedge Maze','Tower of High Sorcery','Inn of the Last Home','Last Heroes Tomb','Academy of Sorcery','Gods Row','Temple of Majere','Temple of Kiri-Jolith','Temple of Mishakal','Temple of Zeboim','The Trough','Sad Town','Xak Tsaroth','Zhaman','Skullcap','Saifhum','Karthay','Mithas','Kothas','Silver Dragon Mountain','Silvanesti'];

	$planet = array_rand($krynn,1);
	$item['location'] = $krynn[$planet];

	return;
}




/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function krynn_settings_post($a,$post) {
	if(! local_user())
		return;
	if($_POST['krynn-submit'])
		PConfig::set(local_user(),'krynn','enable',intval($_POST['krynn']));
}


/**
 *
 * Called from the addon Setting form.
 * Add our own settings info to the page.
 *
 */



function krynn_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/krynn/krynn.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = PConfig::get(local_user(),'krynn','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

    $s .= '<span id="settings_krynn_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_krynn_expanded\'); openClose(\'settings_krynn_inflated\');">';
	$s .= '<h3>' . L10n::t('Krynn') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_krynn_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_krynn_expanded\'); openClose(\'settings_krynn_inflated\');">';
	$s .= '<h3>' . L10n::t('Krynn') . '</h3>';
	$s .= '</span>';


    $s .= '<div class="settings-block">';
	$s .= '<h3>' . L10n::t('Krynn Settings') . '</h3>';
	$s .= '<div id="krynn-enable-wrapper">';
	$s .= '<label id="krynn-enable-label" for="krynn-checkbox">' . L10n::t('Enable Krynn Addon') . '</label>';
	$s .= '<input id="krynn-checkbox" type="checkbox" name="krynn" value="1" ' . $checked . '/>';
        $s .= '</div><div class="clear"></div></div>';
	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="krynn-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

}


