<?php
/**
 * Name: Dragonlance Krynn locales
 * Description: Set a random locale from the Dragonlance Realm of Krynn when posting. Based on the planets friendica addon by Mike Macgirvin and Tony Baldwin
 * Version: 1.0
 * Planets Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Planets Author: Tony Baldwin <https://free-haven.org/profile/tony>
 * Author: Dylan Thiedeke <https://theronin.net/profile/swathe>
 *
 *"My body was my sacrifice... for my magic. This damage is permanent." - Raistlin Majere
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function krynn_install()
{
	/**
	 * Our demo addon will attach in three places.
	 * The first is just prior to storing a local post.
	 */
	Hook::register('post_local', 'addon/krynn/krynn.php', 'krynn_post_hook');

	/**
	 * Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences.
	 */
	Hook::register('addon_settings', 'addon/krynn/krynn.php', 'krynn_settings');
	Hook::register('addon_settings_post', 'addon/krynn/krynn.php', 'krynn_settings_post');

	Logger::notice("installed krynn");
}

function krynn_post_hook(&$item)
{
	/**
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our addon
	 */
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
	$active = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'krynn', 'enable');

	if (!$active) {
		return;
	}

	/**
	 *
	 * OK, we're allowed to do our stuff.
	 * Here's what we are going to do:
	 * load the list of timezone names, and use that to generate a list of krynn locales.
	 * Then we'll pick one of those at random and put it in the "location" field for the post.
	 *
	 */

	$krynn = ['Ansalon','Abanasinia','Solace','Haven','Gateway','Qualinost','Ankatavaka','Pax Tharkas','Ergoth','Newsea','Straights of Schallsea','Plains of Dust','Tarsis','Barren Hills','Que Shu','Citadel of Light','Solinari','Hedge Maze','Tower of High Sorcery','Inn of the Last Home','Last Heroes Tomb','Academy of Sorcery','Gods Row','Temple of Majere','Temple of Kiri-Jolith','Temple of Mishakal','Temple of Zeboim','The Trough','Sad Town','Xak Tsaroth','Zhaman','Skullcap','Saifhum','Karthay','Mithas','Kothas','Silver Dragon Mountain','Silvanesti'];

	$planet = array_rand($krynn,1);
	$item['location'] = $krynn[$planet];

	return;
}

/**
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 */
function krynn_settings_post($post)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if ($_POST['krynn-submit']) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(),'krynn','enable',intval($_POST['krynn']));
	}
}

/**
 * Called from the addon Setting form.
 * Add our own settings info to the page.
 */
function krynn_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(),'krynn','enable');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/krynn/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['krynn', DI::l10n()->t('Enable Krynn Addon'), $enabled],
	]);

	$data = [
		'addon' => 'krynn',
		'title' => DI::l10n()->t('Krynn Settings'),
		'html'  => $html,
	];
}


