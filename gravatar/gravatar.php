<?php
/**
 * Name: Gravatar Support
 * Description: If there is no avatar image for a new user or contact this addon will look for one at Gravatar.
 * Version: 1.1
 * Author: Klaus Weidenbach <http://friendica.dszdw.net/profile/klaus>
 */

use Friendica\App;
use Friendica\BaseModule;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Util\ConfigFileLoader;
use Friendica\Util\Strings;

/**
 * Installs the addon hook
 */
function gravatar_install() {
	Hook::register('load_config',   'addon/gravatar/gravatar.php', 'gravatar_load_config');
	Hook::register('avatar_lookup', 'addon/gravatar/gravatar.php', 'gravatar_lookup');

	Logger::log("registered gravatar in avatar_lookup hook");
}

/**
 * Removes the addon hook
 */
function gravatar_uninstall() {
	Hook::unregister('load_config',   'addon/gravatar/gravatar.php', 'gravatar_load_config');
	Hook::unregister('avatar_lookup', 'addon/gravatar/gravatar.php', 'gravatar_lookup');

	Logger::log("unregistered gravatar in avatar_lookup hook");
}

function gravatar_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('gravatar'));
}

/**
 * Looks up the avatar at gravatar.com and returns the URL.
 *
 * @param $a array
 * @param &$b array
 */
function gravatar_lookup($a, &$b) {
	$default_avatar = DI::config()->get('gravatar', 'default_avatar');
	$rating = DI::config()->get('gravatar', 'rating');

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
	$t = Renderer::getMarkupTemplate( "admin.tpl", "addon/gravatar/" );

	$default_avatar = DI::config()->get('gravatar', 'default_avatar');
	$rating = DI::config()->get('gravatar', 'rating');

	// set default values for first configuration
	if(! $default_avatar)
		$default_avatar = 'identicon'; // pseudo-random geometric pattern based on email hash
	if(! $rating)
		$rating = 'g'; // suitable for display on all websites with any audience type

	// Available options for the select boxes
	$default_avatars = [
		'mm' => DI::l10n()->t('generic profile image'),
		'identicon' => DI::l10n()->t('random geometric pattern'),
		'monsterid' => DI::l10n()->t('monster face'),
		'wavatar' => DI::l10n()->t('computer generated face'),
		'retro' => DI::l10n()->t('retro arcade style face'),
	];
	$ratings = [
		'g' => 'g',
		'pg' => 'pg',
		'r' => 'r',
		'x' => 'x'
	];

	// Check if Libravatar is enabled and show warning
	$r = q("SELECT * FROM `addon` WHERE `name` = '%s' and `installed` = 1",
		DBA::escape('libravatar')
	);
	if (count($r)) {
		$o = '<h5>' .DI::l10n()->t('Information') .'</h5><p>' .DI::l10n()->t('Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.') .'</p><br><br>';
	}

	// output Gravatar settings
	$o .= '<input type="hidden" name="form_security_token" value="' . BaseModule::getFormSecurityToken("gravatarsave") .'">';
	$o .= Renderer::replaceMacros( $t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$default_avatar' => ['avatar', DI::l10n()->t('Default avatar image'), $default_avatar, DI::l10n()->t('Select default avatar image if none was found at Gravatar. See README'), $default_avatars],
		'$rating' => ['rating', DI::l10n()->t('Rating of images'), $rating, DI::l10n()->t('Select the appropriate avatar rating for your site. See README'), $ratings],
	]);
}

/**
 * Save admin settings
 */
function gravatar_addon_admin_post (&$a) {
	BaseModule::checkFormSecurityToken('gravatarsave');

	$default_avatar = (!empty($_POST['avatar']) ? Strings::escapeTags(trim($_POST['avatar'])) : 'identicon');
	$rating = (!empty($_POST['rating']) ? Strings::escapeTags(trim($_POST['rating'])) : 'g');
	DI::config()->set('gravatar', 'default_avatar', $default_avatar);
	DI::config()->set('gravatar', 'rating', $rating);
	info(DI::l10n()->t('Gravatar settings updated.') .EOL);
}
