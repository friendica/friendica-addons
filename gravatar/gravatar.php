<?php
/**
 * Name: Gravatar Support
 * Description: If there is no avatar image for a new user or contact this plugin will look for one at Gravatar.
 * Version: 1.0
 * Author: Klaus Weidenbach <http://friendica.dszdw.net/profile/klaus>
 */

/**
 * Installs the plugin hook
 */
function gravatar_install() {
	register_hook('avatar_lookup', 'addon/gravatar/gravatar.php', 'gravatar_lookup');

	logger("installed gravatar");
}

/**
 * Removes the plugin hook
 */
function gravatar_uninstall() {
	unregister_hook('avatar_lookup', 'addon/gravatar/gravatar.php', 'gravatar_lookup');

	logger("uninstalled gravatar");
}

/**
 * Looks up the avatar at gravatar.com and returns the URL.
 *
 * @param $a array
 * @param &$b array
 */
function gravatar_lookup($a, &$b) {
	$default_avatar = get_config('gravatar', 'default_img');
	$rating = get_config('gravatar', 'rating');

	// setting default value if nothing configured
	if(! $default_avatar)
		$default_avatar = 'identicon'; // default image will be a random pattern
	if(! $rating)
		$rating = 'g'; // suitable for display on all websites with any audience type

	$hash = md5(trim(strtolower($b['email'])));

	$url = 'http://www.gravatar.com/avatar/' .$hash .'.jpg';
	$url .= '?s=' .$b['size'] .'&r=' .$rating;
	if ($default_avatar != "gravatar")
		$url .= '&d=' .$default_avatar;

	$b['url'] = $url;	
	$b['success'] = true;
}

/**
 * Display admin settings for this addon
 */
function gravatar_plugin_admin (&$a, &$o) {
	$t = file_get_contents( dirname(__file__)."/admin.tpl");

	$default_avatar = get_config('gravatar', 'default_img');
	$rating = get_config('gravatar', 'rating');

	// set default values for first configuration
	if(! $default_avatar)
		$default_avatar = 'identicon'; // pseudo-random geometric pattern based on email hash
	if(! $rating)
		$rating = 'g'; // suitable for display on all websites with any audience type

	// Available options for the select boxes
	$default_avatars = array(
		'mm' => t('generic profile image'),
		'identicon' => t('random geometric pattern'),
		'monsterid' => t('monster face'),
		'wavatar' => t('computer generated face'),
		'retro' => t('retro arcade style face'),
	);
	$ratings = array(
		'g' => 'g',
		'pg' => 'pg',
		'r' => 'r',
		'x' => 'x'
	);

	$o = '<input type="hidden" name="form_security_token" value="' .get_form_security_token("gravatarsave") .'">';
	$o .= replace_macros( $t, array(
		'$submit' => t('Submit'),
		'$default_avatar' => array('avatar', t('Default avatar image'), $default_avatar, t('Select default avatar image if none was found at Gravatar. See README'), $default_avatars),
		'$rating' => array('rating', t('Rating of images'), $rating, t('Select the appropriate avatar rating for your site. See README'), $ratings),
	));
}

/**
 * Save admin settings
 */
function gravatar_plugin_admin_post (&$a) {
	check_form_security_token('gravatarsave');

	$default_avatar = ((x($_POST, 'avatar')) ? notags(trim($_POST['avatar'])) : 'identicon');
	$rating = ((x($_POST, 'rating')) ? notags(trim($_POST['rating'])) : 'g');
	set_config('gravatar', 'default_img', $default_avatar);
	set_config('gravatar', 'rating', $rating);
	info( t('Gravatar settings updated.') .EOL);
}
?>
