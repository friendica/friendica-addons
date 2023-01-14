<?php
/**
 * Name: Random place
 * Description: Sample Friendica addon. Set a random place when posting.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 *
 *
 *
 * Addons are registered with the system through the admin
 * panel.
 *
 * When registration is detected, the system calls the addon
 * name_install() function, located in 'addon/name/name.php',
 * where 'name' is the name of the addon.
 * If the addon is removed from the configuration list, the
 * system will call the name_uninstall() function.
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function randplace_install()
{
	/*
	 * Our demo addon will attach in three places.
	 * The first is just prior to storing a local post.
	 */
	Hook::register('post_local', 'addon/randplace/randplace.php', 'randplace_post_hook');

	/*
	 * Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences.
	 */
	Hook::register('addon_settings', 'addon/randplace/randplace.php', 'randplace_settings');
	Hook::register('addon_settings_post', 'addon/randplace/randplace.php', 'randplace_settings_post');

	Logger::notice("installed randplace");
}

function randplace_uninstall()
{
	/*
	 * This function should undo anything that was done in name_install()
	 *
	 * Except hooks, they are all unregistered automatically and don't need to be unregistered manually.
	 */
	Logger::notice("removed randplace");
}

function randplace_post_hook(&$item)
{
	/*
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our addon
	 */
	Logger::notice('randplace invoked');

	if (!DI::userSession()->getLocalUserId()) {
		/* non-zero if this is a logged in user of this system */
		return;
	}

	if (DI::userSession()->getLocalUserId() != $item['uid']) {
		/* Does this person own the post? */
		return;
	}

	if ($item['parent']) {
		/* If the item has a parent, this is a comment or something else, not a status post. */
		return;
	}

	/* Retrieve our personal config setting */

	$active = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'randplace', 'enable');

	if (!$active) {
		return;
	}

	/**
	 *
	 * OK, we're allowed to do our stuff.
	 * Here's what we are going to do:
	 * load the list of timezone names, and use that to generate a list of world cities.
	 * Then we'll pick one of those at random and put it in the "location" field for the post.
	 *
	 */

	$cities = [];
	$zones = timezone_identifiers_list();
	foreach($zones as $zone) {
		if ((strpos($zone, '/')) && (! stristr($zone, 'US/')) && (! stristr($zone, 'Etc/'))) {
			$cities[] = str_replace('_', ' ',substr($zone, strpos($zone, '/') + 1));
		}
	}

	if (!count($cities)) {
		return;
	}

	$city = array_rand($cities,1);
	$item['location'] = $cities[$city];

	return;
}

/**
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 */
function randplace_settings_post($post)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if ($_POST['randplace-submit']) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'randplace', 'enable', intval($_POST['randplace']));
	}
}


/**
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 */
function randplace_settings(array &$data)
{
	if(!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(),'randplace','enable');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/randplace/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['randplace', DI::l10n()->t('Enable Randplace Addon'), $enabled],
	]);

	$data = [
		'addon' => 'randplace',
		'title' => DI::l10n()->t('Randplace Settings'),
		'html'  => $html,
	];
}
