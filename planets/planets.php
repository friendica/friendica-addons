<?php
/**
 * Name: Random Planet, Empirial Version
 * Description: Sample Friendica plugin/addon. Set a random planet from the Emprire when posting.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Darth Baldwin
 * 
 * 
 * 
 *
 * Addons are registered with the system in the
 * .htconfig.php file.
 *
 * $a->config['system']['addon'] = 'plugin1,plugin2,etc.';
 *
 * When registration is detected, the system calls the plugin
 * name_install() function, located in 'addon/name/name.php',
 * where 'name' is the name of the addon.
 * If the addon is removed from the configuration list, the 
 * system will call the name_uninstall() function.
 *
 */


function planets_install() {

	/**
	 * 
	 * Our demo plugin will attach in three places.
	 * The first is just prior to storing a local post.
	 *
	 */

	register_hook('post_local', 'addon/planets/planets.php', 'planets_post_hook');

	/**
	 *
	 * Then we'll attach into the plugin settings page, and also the 
	 * settings post hook so that we can create and update
	 * user preferences.
	 *
	 */

	register_hook('plugin_settings', 'addon/planets/planets.php', 'planets_settings');
	register_hook('plugin_settings_post', 'addon/planets/planets.php', 'planets_settings_post');

	logger("installed planets");
}


function planets_uninstall() {

	/**
	 *
	 * uninstall unregisters any hooks created with register_hook
	 * during install. It may also delete configuration settings
	 * and any other cleanup.
	 *
	 */

	unregister_hook('post_local',    'addon/planets/planets.php', 'planets_post_hook');
	unregister_hook('plugin_settings', 'addon/planets/planets.php', 'planets_settings');
	unregister_hook('plugin_settings_post', 'addon/planets/planets.php', 'planets_settings_post');


	logger("removed planets");
}



function planets_post_hook($a, &$item) {

	/**
	 *
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our plugin
	 *
	 */

	logger('planets invoked');

	if(! local_user())   /* non-zero if this is a logged in user of this system */
		return;

	if(local_user() != $item['uid'])    /* Does this person own the post? */
		return;

	if($item['parent'])   /* If the item has a parent, this is a comment or something else, not a status post. */
		return;

	/* Retrieve our personal config setting */

	$active = get_pconfig(local_user(), 'planets', 'enable');

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

	$planets = array('Alderaan','Tatooine','Dagoba','Polis Massa','Coruscant','Hoth','Endor','Kamino','Rattatak','Mustafar','Iego','Geonosis','Felucia','Dantooine','Ansion','Artaru','Bespin','Boz Pity','Cato Neimoidia','Christophsis','Kashyyk','Kessel','Malastare','Mygeeto','Nar Shaddaa','Ord Mantell','Saleucami','Subterrel','Death Star','Teth','Tund','Utapau','Yavin');
# 	$zones = timezone_identifiers_list();
# 	foreach($zones as $zone) {
# 		if((strpos($zone,'/')) && (! stristr($zone,'US/')) && (! stristr($zone,'Etc/')))
# 			$planets[] = str_replace('_', ' ',substr($zone,strpos($zone,'/') + 1));
# 	}
# 
# 	if(! count($planets))
# 		return;
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
		set_pconfig(local_user(),'planets','enable',intval($_POST['planets']));
}


/**
 *
 * Called from the Plugin Setting form. 
 * Add our own settings info to the page.
 *
 */



function planets_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/planets/planets.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = get_pconfig(local_user(),'planets','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Planets Settings') . '</h3>';
	$s .= '<div id="planets-enable-wrapper">';
	$s .= '<label id="planets-enable-label" for="planets-checkbox">' . t('Enable Planets Plugin') . '</label>';
	$s .= '<input id="planets-checkbox" type="checkbox" name="planets" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="planets-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}
