<?php
/**
 * Name: Libravatar Support
 * Description: If there is no avatar image for a new user or contact this addon will look for one at Libravatar. Please disable Gravatar addon if you use this one. (requires PHP >= 5.3)
 * Version: 1.1
 * Author: Klaus Weidenbach <http://friendica.dszdw.net/profile/klaus>
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Core\Config\Cache\ConfigFileLoader;
use Friendica\Util\Strings;

/**
 * Installs the addon hook
 */
function libravatar_install()
{
	Hook::register('load_config',   'addon/libravatar/libravatar.php', 'libravatar_load_config');
	Hook::register('avatar_lookup', 'addon/libravatar/libravatar.php', 'libravatar_lookup');
	Logger::notice("registered libravatar in avatar_lookup hook");
}

function libravatar_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('libravatar'));
}

/**
 * Looks up the avatar at Libravatar and returns the URL.
 *
 * @param $a array
 * @param &$b array
 */
function libravatar_lookup($a, &$b)
{
	$default_avatar = DI::config()->get('libravatar', 'default_avatar');
	if (empty($default_avatar)) {
		// if not set, look up if there was one from the gravatar addon
		$default_avatar = DI::config()->get('gravatar', 'default_avatar', 'identicon');
	}

	require_once 'Services/Libravatar.php';
	$libravatar = new Services_Libravatar();
	$libravatar->setSize($b['size']);
	$libravatar->setDefault($default_avatar);
	$avatar_url = $libravatar->getUrl($b['email']);

	$b['url'] = $avatar_url;
	$b['success'] = true;
}

/**
 * Display admin settings for this addon
 */
function libravatar_addon_admin(&$a, &$o)
{
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/libravatar");

	$default_avatar = DI::config()->get('libravatar', 'default_avatar', 'identicon');

	// Available options for the select boxes
	$default_avatars = [
		'mm' => DI::l10n()->t('generic profile image'),
		'identicon' => DI::l10n()->t('random geometric pattern'),
		'monsterid' => DI::l10n()->t('monster face'),
		'wavatar' => DI::l10n()->t('computer generated face'),
		'retro' => DI::l10n()->t('retro arcade style face'),
		'robohash' => DI::l10n()->t('roboter face'),
		'pagan' => DI::l10n()->t('retro adventure game character'),
	];

	if (Addon::isEnabled('gravatar')) {
		$o = '<h5>' .DI::l10n()->t('Information') .'</h5><p>' .DI::l10n()->t('Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.') .'</p><br><br>';
	}

	// output Libravatar settings
	$o .= Renderer::replaceMacros( $t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$default_avatar' => ['avatar', DI::l10n()->t('Default avatar image'), $default_avatar, DI::l10n()->t('Select default avatar image if none was found. See README'), $default_avatars],
	]);
}

/**
 * Save admin settings
 */
function libravatar_addon_admin_post(&$a)
{
	$default_avatar = (!empty($_POST['avatar']) ? Strings::escapeTags(trim($_POST['avatar'])) : 'identicon');
	DI::config()->set('libravatar', 'default_avatar', $default_avatar);
}
