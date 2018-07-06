<?php
/**
 * Name: Geonames
 * Description: Use Geonames service to resolve nearest populated location for given latitude, longitude
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 *
 * Pre-requisite: Register a username at geonames.org
 * and set in config/addon.ini.php
 *
 * [geonames]
 * username = your_username
 *
 * Also visit http://geonames.org/manageaccount and enable access to the free web services
 *
 * When addon is installed, the system calls the addon
 * name_install() function, located in 'addon/name/name.php',
 * where 'name' is the name of the addon.
 * If the addon is removed from the configuration list, the
 * system will call the name_uninstall() function.
 *
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;
use Friendica\Util\Network;
use Friendica\Util\XML;

function geonames_install() {

	Addon::registerHook('load_config', 'addon/geonames/geonames.php', 'geonames_load_config');

	/**
	 *
	 * Our addon will attach in three places.
	 * The first is just prior to storing a local post.
	 *
	 */

	Addon::registerHook('post_local', 'addon/geonames/geonames.php', 'geonames_post_hook');

	/**
	 *
	 * Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences.
	 *
	 */

	Addon::registerHook('addon_settings', 'addon/geonames/geonames.php', 'geonames_addon_admin');
	Addon::registerHook('addon_settings_post', 'addon/geonames/geonames.php', 'geonames_addon_admin_post');

	logger("installed geonames");
}


function geonames_uninstall() {

	/**
	 *
	 * uninstall unregisters any hooks created with register_hook
	 * during install. It may also delete configuration settings
	 * and any other cleanup.
	 *
	 */

	Addon::unregisterHook('load_config',   'addon/geonames/geonames.php', 'geonames_load_config');
	Addon::unregisterHook('post_local',    'addon/geonames/geonames.php', 'geonames_post_hook');
	Addon::unregisterHook('addon_settings', 'addon/geonames/geonames.php', 'geonames_addon_admin');
	Addon::unregisterHook('addon_settings_post', 'addon/geonames/geonames.php', 'geonames_addon_admin_post');


	logger("removed geonames");
}

function geonames_load_config(\Friendica\App $a)
{
	$a->loadConfigFile(__DIR__. '/config/geonames.ini.php');
}

function geonames_post_hook($a, &$item) {

	/**
	 *
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our addon
	 *
	 */

	logger('geonames invoked');

	if(! local_user())   /* non-zero if this is a logged in user of this system */
		return;

	if(local_user() != $item['uid'])    /* Does this person own the post? */
		return;

	if($item['parent'])   /* If the item has a parent, this is a comment or something else, not a status post. */
		return;

	/* Retrieve our personal config setting */

	$geo_account = Config::get('geonames', 'username');
	$active = PConfig::get(local_user(), 'geonames', 'enable');

	if((! $geo_account) || (! $active))
		return;

	if((! $item['coord']) || ($item['location']))
		return;

	$coords = explode(' ',$item['coord']);

	/**
	 *
	 * OK, we're allowed to do our stuff.
	 *
	 */

	$s = Network::fetchUrl('http://api.geonames.org/findNearbyPlaceName?lat=' . $coords[0] . '&lng=' . $coords[1] . '&username=' . $geo_account);

	if(! $s)
		return;

	$xml = XML::parseString($s);

	if($xml->geoname->name && $xml->geoname->countryName)
		$item['location'] = $xml->geoname->name . ', ' . $xml->geoname->countryName;


//	logger('geonames : ' . print_r($xml,true), LOGGER_DATA);
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

function geonames_addon_admin_post($a,$post) {
	if(! local_user() || (! x($_POST,'geonames-submit')))
		return;
	PConfig::set(local_user(),'geonames','enable',intval($_POST['geonames']));

	info(L10n::t('Geonames settings updated.') . EOL);
}


/**
 *
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 */



function geonames_addon_admin(App $a, &$s) {

	if(! local_user())
		return;

	$geo_account = Config::get('geonames', 'username');

	if(! $geo_account)
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/geonames/geonames.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = PConfig::get(local_user(),'geonames','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . L10n::t('Geonames Settings') . '</h3>';
	$s .= '<div id="geonames-enable-wrapper">';
	$s .= '<label id="geonames-enable-label" for="geonames-checkbox">' . L10n::t('Enable Geonames Addon') . '</label>';
	$s .= '<input id="geonames-checkbox" type="checkbox" name="geonames" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="geonames-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

}
