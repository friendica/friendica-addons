<?php
/**
 * Name: Geonames
 * Description: Use Geonames service to resolve nearest populated location for given latitude, longitude
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Core\Config\Util\ConfigFileManager;
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

function geonames_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('geonames'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function geonames_post_hook(array &$item)
{
	/* An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our addon
	 */

	Logger::notice('geonames invoked');

	if (!DI::userSession()->getLocalUserId()) {   /* non-zero if this is a logged in user of this system */
		return;
	}

	if (DI::userSession()->getLocalUserId() != $item['uid']) {   /* Does this person own the post? */
		return;
	}

	if ($item['parent']) {   /* If the item has a parent, this is a comment or something else, not a status post. */
		return;
	}

	/* Retrieve our personal config setting */

	$geo_account = DI::config()->get('geonames', 'username');
	$active = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'geonames', 'enable');

	if (!$geo_account || !$active) {
		return;
	}

	if (!$item['coord'] || $item['location']) {
		return;
	}

	$coords = explode(' ', $item['coord']);

	/* OK, we're allowed to do our stuff. */

	$s = DI::httpClient()->fetch('http://api.geonames.org/findNearbyPlaceName?lat=' . $coords[0] . '&lng=' . $coords[1] . '&username=' . $geo_account);

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
 * @param array $post The $_POST array
 */
function geonames_addon_settings_post(array $post)
{
	if (!DI::userSession()->getLocalUserId() || empty($_POST['geonames-submit'])) {
		return;
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'geonames', 'enable', intval($_POST['geonames-enable']));
}

/**
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 * @param array $data
 * @throws Exception
 */
function geonames_addon_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$geo_account = DI::config()->get('geonames', 'username');
	if (!$geo_account) {
		return;
	}

	$enabled = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'geonames', 'enable'));

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/geonames/');
	$html = Renderer::replaceMacros($t, [
		'$info'   => DI::l10n()->t('Replace numerical coordinates by the nearest populated location name in your posts.'),
		'$enable' => ['geonames-enable', DI::l10n()->t('Enable Geonames Addon'), $enabled],
	]);

	$data = [
		'addon' => 'geonames',
		'title' => DI::l10n()->t('Geonames Settings'),
		'html'  => $html,
	];
}
