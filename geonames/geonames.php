<?php
/**
 * Name: Geonames
 * Description: Use Geonames service to resolve nearest populated location for given latitude, longitude
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

use Friendica\App;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\ConfigFileLoader;
use Friendica\Util\Network;
use Friendica\Util\XML;

function geonames_install()
{
	Hook::register('load_config', __FILE__, 'geonames_load_config');

	/* Our addon will attach in three places.
	 * The first is just prior to storing a local post.
	 */

	Hook::register('post_local', __FILE__, 'geonames_post_hook');

	/* Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences.
	 */

	Hook::register('addon_settings', __FILE__, 'geonames_addon_settings');
	Hook::register('addon_settings_post', __FILE__, 'geonames_addon_settings_post');
}

function geonames_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('geonames'));
}

function geonames_post_hook(App $a, array &$item)
{
	/* An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our addon
	 */

	Logger::log('geonames invoked');

	if (!local_user()) {   /* non-zero if this is a logged in user of this system */
		return;
	}

	if (local_user() != $item['uid']) {   /* Does this person own the post? */
		return;
	}

	if ($item['parent']) {   /* If the item has a parent, this is a comment or something else, not a status post. */
		return;
	}

	/* Retrieve our personal config setting */

	$geo_account = Config::get('geonames', 'username');
	$active = DI::pConfig()->get(local_user(), 'geonames', 'enable');

	if (!$geo_account || !$active) {
		return;
	}

	if (!$item['coord'] || $item['location']) {
		return;
	}

	$coords = explode(' ', $item['coord']);

	/* OK, we're allowed to do our stuff. */

	$s = Network::fetchUrl('http://api.geonames.org/findNearbyPlaceName?lat=' . $coords[0] . '&lng=' . $coords[1] . '&username=' . $geo_account);

	if (!$s) {
		return;
	}

	$xml = XML::parseString($s);

	if ($xml->geoname->name && $xml->geoname->countryName) {
		$item['location'] = $xml->geoname->name . ', ' . $xml->geoname->countryName;
	}

	return;
}

/**
 * Callback from the settings post function.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 * @param App   $a
 * @param array $post The $_POST array
 */
function geonames_addon_settings_post(App $a, array $post)
{
	if (!local_user() || empty($_POST['geonames-submit'])) {
		return;
	}

	DI::pConfig()->set(local_user(), 'geonames', 'enable', intval($_POST['geonames-enable']));

	info(L10n::t('Geonames settings updated.'));
}

/**
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 * @param App    $a
 * @param string $s
 * @throws Exception
 */
function geonames_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$geo_account = Config::get('geonames', 'username');

	if (!$geo_account) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */
	$stylesheetPath = __DIR__ . '/geonames.css';
	DI::page()->registerStylesheet($stylesheetPath);

	/* Get the current state of our config variable */
	$enabled = intval(DI::pConfig()->get(local_user(), 'geonames', 'enable'));

	$t = Renderer::getMarkupTemplate('settings.tpl', __DIR__);
	$s .= Renderer::replaceMacros($t, [
		'$title' => L10n::t('Geonames Settings'),
		'$description' => L10n::t('Replace numerical coordinates by the nearest populated location name in your posts.'),
		'$enable' => ['geonames-enable', L10n::t('Enable Geonames Addon'), $enabled],
		'$submit' => L10n::t('Save Settings')
	]);
}
