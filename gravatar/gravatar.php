<?php
/**
 * Name: Gravatar Support
 * Description: If there is no avatar image for a new user or contact this addon will look for one at Gravatar.
 * Version: 1.1
 * Author: Klaus Weidenbach <http://friendica.dszdw.net/profile/klaus>
 */
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;

/**
 * Installs the addon hook
 */
function gravatar_install() {
	Addon::registerHook('avatar_lookup', 'addon/gravatar/gravatar.php', 'gravatar_lookup');

	logger("registered gravatar in avatar_lookup hook");
}

/**
 * Removes the addon hook
 */
function gravatar_uninstall() {
	Addon::unregisterHook('avatar_lookup', 'addon/gravatar/gravatar.php', 'gravatar_lookup');

	logger("unregistered gravatar in avatar_lookup hook");
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

	$url = 'https://secure.gravatar.com/avatar/' .$hash .'.jpg';
	$url .= '?s=' .$b['size'] .'&r=' .$rating;
	if ($default_avatar != "gravatar")
		$url .= '&d=' .$default_avatar;

	$b['url'] = $url;	
	$b['success'] = true;
}

/**
 * Display admin settings for this addon
 */
function gravatar_addon_admin (&$a, &$o) {
	$t = get_markup_template( "admin.tpl", "addon/gravatar/" );

	$default_avatar = get_config('gravatar', 'default_img');
	$rating = get_config('gravatar', 'rating');

	// set default values for first configuration
	if(! $default_avatar)
		$default_avatar = 'identicon'; // pseudo-random geometric pattern based on email hash
	if(! $rating)
		$rating = 'g'; // suitable for display on all websites with any audience type

	// Available options for the select boxes
	$default_avatars = [
		'mm' => L10n::t('generic profile image'),
		'identicon' => L10n::t('random geometric pattern'),
		'monsterid' => L10n::t('monster face'),
		'wavatar' => L10n::t('computer generated face'),
		'retro' => L10n::t('retro arcade style face'),
	];
	$ratings = [
		'g' => 'g',
		'pg' => 'pg',
		'r' => 'r',
		'x' => 'x'
	);

	// Check if Libravatar is enabled and show warning
	$r = q("SELECT * FROM `addon` WHERE `name` = '%s' and `installed` = 1",
		dbesc('libravatar')
	);
	if (count($r)) {
		$o = '<h5>' .L10n::t('Information') .'</h5><p>' .L10n::t('Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.') .'</p><br><br>';
	}

	// output Gravatar settings
	$o .= '<input type="hidden" name="form_security_token" value="' .get_form_security_token("gravatarsave") .'">';
	$o .= replace_macros( $t, [
		'$submit' => L10n::t('Save Settings'),
		'$default_avatar' => ['avatar', L10n::t('Default avatar image'), $default_avatar, L10n::t('Select default avatar image if none was found at Gravatar. See README'), $default_avatars],
		'$rating' => ['rating', L10n::t('Rating of images'), $rating, L10n::t('Select the appropriate avatar rating for your site. See README'), $ratings],
	]);
}

/**
 * Save admin settings
 */
function gravatar_addon_admin_post (&$a) {
	check_form_security_token('gravatarsave');

	$default_avatar = ((x($_POST, 'avatar')) ? notags(trim($_POST['avatar'])) : 'identicon');
	$rating = ((x($_POST, 'rating')) ? notags(trim($_POST['rating'])) : 'g');
	Config::set('gravatar', 'default_img', $default_avatar);
	Config::set('gravatar', 'rating', $rating);
	info(L10n::t('Gravatar settings updated.') .EOL);
}
