<?php
/**
 * Name: Geonames
 * Description: Use Geonames service to resolve nearest populated location for given latitude, longitude
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 * 
 * Pre-requisite: Register a username at geonames.org
 * and set in .htconfig.php
 *
 * $a->config['geonames']['username'] = 'your_username';
 * Also visit http://geonames.org/manageaccount and enable access to the free web services
 *
 * When plugin is installed, the system calls the plugin
 * name_install() function, located in 'addon/name/name.php',
 * where 'name' is the name of the addon.
 * If the addon is removed from the configuration list, the 
 * system will call the name_uninstall() function.
 *
 */


function geonames_install() {

	/**
	 * 
	 * Our plugin will attach in three places.
	 * The first is just prior to storing a local post.
	 *
	 */

	register_hook('post_local', 'addon/geonames/geonames.php', 'geonames_post_hook');

	/**
	 *
	 * Then we'll attach into the plugin settings page, and also the 
	 * settings post hook so that we can create and update
	 * user preferences.
	 *
	 */

	register_hook('plugin_settings', 'addon/geonames/geonames.php', 'geonames_plugin_admin');
	register_hook('plugin_settings_post', 'addon/geonames/geonames.php', 'geonames_plugin_admin_post');

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

	unregister_hook('post_local',    'addon/geonames/geonames.php', 'geonames_post_hook');
	unregister_hook('plugin_settings', 'addon/geonames/geonames.php', 'geonames_plugin_admin');
	unregister_hook('plugin_settings_post', 'addon/geonames/geonames.php', 'geonames_plugin_admin_post');


	logger("removed geonames");
}



function geonames_post_hook($a, &$item) {

	/**
	 *
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our plugin
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

	$geo_account = get_config('geonames', 'username');
	$active = get_pconfig(local_user(), 'geonames', 'enable');

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

	$s = fetch_url('http://api.geonames.org/findNearbyPlaceName?lat=' . $coords[0] . '&lng=' . $coords[1] . '&username=' . $geo_account);

	if(! $s)
		return;

	$xml = parse_xml_string($s);

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

function geonames_plugin_admin_post($a,$post) {
	if(! local_user() || (! x($_POST,'geonames-submit')))
		return;
	set_pconfig(local_user(),'geonames','enable',intval($_POST['geonames']));

	info( t('Geonames settings updated.') . EOL);
}


/**
 *
 * Called from the Plugin Setting form. 
 * Add our own settings info to the page.
 *
 */



function geonames_plugin_admin(&$a,&$s) {

	if(! local_user())
		return;

	$geo_account = get_config('geonames', 'username');

	if(! $geo_account)
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/geonames/geonames.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = get_pconfig(local_user(),'geonames','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Geonames Settings') . '</h3>';
	$s .= '<div id="geonames-enable-wrapper">';
	$s .= '<label id="geonames-enable-label" for="geonames-checkbox">' . t('Enable Geonames Plugin') . '</label>';
	$s .= '<input id="geonames-checkbox" type="checkbox" name="geonames" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="geonames-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}
