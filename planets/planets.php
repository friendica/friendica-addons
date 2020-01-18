<?php
/**
 * Name: Random Planet, Empirial Version
 * Description: Sample Friendica addon. Set a random planet from the Emprire when posting.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Tony Baldwin <https://free-haven.org/profile/tony>
 */
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\DI;

function planets_install() {

	/**
	 *
	 * Our demo addon will attach in three places.
	 * The first is just prior to storing a local post.
	 *
	 */

	Hook::register('post_local', 'addon/planets/planets.php', 'planets_post_hook');

	/**
	 *
	 * Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences.
	 *
	 */

	Hook::register('addon_settings', 'addon/planets/planets.php', 'planets_settings');
	Hook::register('addon_settings_post', 'addon/planets/planets.php', 'planets_settings_post');

	Logger::log("installed planets");
}


function planets_uninstall() {

	/**
	 *
	 * uninstall unregisters any hooks created with register_hook
	 * during install. It may also delete configuration settings
	 * and any other cleanup.
	 *
	 */

	Hook::unregister('post_local',    'addon/planets/planets.php', 'planets_post_hook');
	Hook::unregister('addon_settings', 'addon/planets/planets.php', 'planets_settings');
	Hook::unregister('addon_settings_post', 'addon/planets/planets.php', 'planets_settings_post');


	Logger::log("removed planets");
}



function planets_post_hook($a, &$item) {

	/**
	 *
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our addon
	 *
	 */

	Logger::log('planets invoked');

	if(! local_user())   /* non-zero if this is a logged in user of this system */
		return;

	if(local_user() != $item['uid'])    /* Does this person own the post? */
		return;

	if($item['parent'])   /* If the item has a parent, this is a comment or something else, not a status post. */
		return;

	/* Retrieve our personal config setting */

	$active = DI::pConfig()->get(local_user(), 'planets', 'enable');

	if(! $active)
		return;

	/**
	 *
	 * OK, we're allowed to do our stuff.
	 * Here's what we are going to do:
	 * load the list of timezone names, and use that to generate a list of world planets.
	 * Then we'll pick one of those at random and put it in the "location" field for the post.
	 *
	 */

	$planets = ['Alderaan','Tatooine','Dagobah','Polis Massa','Coruscant','Hoth','Endor','Kamino','Rattatak','Mustafar','Iego','Geonosis','Felucia','Dantooine','Ansion','Artaru','Bespin','Boz Pity','Cato Neimoidia','Christophsis','Kashyyyk','Kessel','Malastare','Mygeeto','Nar Shaddaa','Ord Mantell','Saleucami','Subterrel','Death Star','Teth','Tund','Utapau','Yavin'];

	$planet = array_rand($planets,1);
	$item['location'] = $planets[$planet];

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

function planets_settings_post($a,$post) {
	if(! local_user())
		return;
	if($_POST['planets-submit'])
		DI::pConfig()->set(local_user(),'planets','enable',intval($_POST['planets']));
}


/**
 *
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 */



function planets_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/planets/planets.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = DI::pConfig()->get(local_user(),'planets','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

    $s .= '<span id="settings_planets_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_planets_expanded\'); openClose(\'settings_planets_inflated\');">';
	$s .= '<h3>' . DI::l10n()->t('Planets') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_planets_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_planets_expanded\'); openClose(\'settings_planets_inflated\');">';
	$s .= '<h3>' . DI::l10n()->t('Planets') . '</h3>';
	$s .= '</span>';

    $s .= '<div class="settings-block">';
	$s .= '<h3>' . DI::l10n()->t('Planets Settings') . '</h3>';
	$s .= '<div id="planets-enable-wrapper">';
	$s .= '<label id="planets-enable-label" for="planets-checkbox">' . DI::l10n()->t('Enable Planets Addon') . '</label>';
	$s .= '<input id="planets-checkbox" type="checkbox" name="planets" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="planets-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';

}
