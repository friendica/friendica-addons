<?php
/**
 * Name: Libravatar Support
 * Description: If there is no avatar image for a new user or contact this addon will look for one at Libravatar. Please disable Gravatar addon if you use this one. (requires PHP >= 5.3)
 * Version: 1.1
 * Author: Klaus Weidenbach <http://friendica.dszdw.net/profile/klaus>
 */

use Friendica\App;
use Friendica\BaseModule;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\Util\Security;
use Friendica\Util\Strings;

/**
 * Installs the addon hook
 */
function libravatar_install()
{
	Addon::registerHook('load_config',   'addon/libravatar/libravatar.php', 'libravatar_load_config');
	Addon::registerHook('avatar_lookup', 'addon/libravatar/libravatar.php', 'libravatar_lookup');
	Logger::log("registered libravatar in avatar_lookup hook");
}

/**
 * Removes the addon hook
 */
function libravatar_uninstall()
{
	Addon::unregisterHook('load_config',   'addon/libravatar/libravatar.php', 'libravatar_load_config');
	Addon::unregisterHook('avatar_lookup', 'addon/libravatar/libravatar.php', 'libravatar_lookup');
	Logger::log("unregistered libravatar in avatar_lookup hook");
}

function libravatar_load_config(App $a)
{
	$a->loadConfigFile(__DIR__ . '/config/libravatar.config.php');
}

/**
 * Looks up the avatar at Libravatar and returns the URL.
 *
 * @param $a array
 * @param &$b array
 */
function libravatar_lookup($a, &$b)
{
	$default_avatar = Config::get('libravatar', 'default_avatar');

	if (! $default_avatar) {
		// if not set, look up if there was one from the gravatar addon
		$default_avatar = Config::get('gravatar', 'default_avatar');
		// setting default avatar if nothing configured
		if (!$default_avatar) {
			$default_avatar = 'identicon'; // default image will be a random pattern
		}
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

	$default_avatar = Config::get('libravatar', 'default_avatar');

	// set default values for first configuration
	if (!$default_avatar) {
		$default_avatar = 'identicon'; // pseudo-random geometric pattern based on email hash
	}

	// Available options for the select boxes
	$default_avatars = [
		'mm' => L10n::t('generic profile image'),
		'identicon' => L10n::t('random geometric pattern'),
		'monsterid' => L10n::t('monster face'),
		'wavatar' => L10n::t('computer generated face'),
		'retro' => L10n::t('retro arcade style face'),
	];

	// Show warning if PHP version is too old
	if (! version_compare(PHP_VERSION, '5.3.0', '>=')) {
		$o = '<h5>' .L10n::t('Warning') .'</h5><p>';
		$o .= L10n::t('Your PHP version %s is lower than the required PHP >= 5.3.', PHP_VERSION);
		$o .= '<br>' .L10n::t('This addon is not functional on your server.') .'<p><br>';
		return;
	}

	// Libravatar falls back to gravatar, so show warning about gravatar addon if enabled
	$r = q("SELECT * FROM `addon` WHERE `name` = '%s' and `installed` = 1",
		DBA::escape('gravatar')
	);
	if (count($r)) {
		$o = '<h5>' .L10n::t('Information') .'</h5><p>' .L10n::t('Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.') .'</p><br><br>';
	}

	// output Libravatar settings
	$o .= '<input type="hidden" name="form_security_token" value="' . BaseModule::getFormSecurityToken("libravatarsave") .'">';
	$o .= Renderer::replaceMacros( $t, [
		'$submit' => L10n::t('Save Settings'),
		'$default_avatar' => ['avatar', L10n::t('Default avatar image'), $default_avatar, L10n::t('Select default avatar image if none was found. See README'), $default_avatars],
	]);
}

/**
 * Save admin settings
 */
function libravatar_addon_admin_post(&$a)
{
	BaseModule::checkFormSecurityToken('libravatarrsave');

	$default_avatar = ((x($_POST, 'avatar')) ? Strings::escapeTags(trim($_POST['avatar'])) : 'identicon');
	Config::set('libravatar', 'default_avatar', $default_avatar);
	info(L10n::t('Libravatar settings updated.') .EOL);
}
