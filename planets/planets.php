<?php
/**
 * Name: Random Planet, Empirial Version
 * Description: Sample Friendica addon. Set a random planet from the Emprire when posting.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Tony Baldwin <https://free-haven.org/profile/tony>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function planets_install()
{
	/**
	 * Our demo addon will attach in three places.
	 * The first is just prior to storing a local post.
	 */
	Hook::register('post_local', 'addon/planets/planets.php', 'planets_post_hook');

	/**
	 * Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences.
	 */
	Hook::register('addon_settings', 'addon/planets/planets.php', 'planets_settings');
	Hook::register('addon_settings_post', 'addon/planets/planets.php', 'planets_settings_post');

	Logger::notice("installed planets");
}

/**
 * An item was posted on the local system.
 * We are going to look for specific items:
 *      - A status post by a profile owner
 *      - The profile owner must have allowed our addon
 */
function planets_post_hook(&$item)
{
	Logger::notice('planets invoked');

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
	$active = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'planets', 'enable');

	if (!$active) {
		return;
	}

	/**
	 *
	 * OK, we're allowed to do our stuff.
	 * Here's what we are going to do:
	 * load the list of timezone names, and use that to generate a list of world planets.
	 * Then we'll pick one of those at random and put it in the "location" field for the post.
	 *
	 */

	$planets = ['Alderaan','Tatooine','Dagobah','Polis Massa','Coruscant','Hoth','Endor','Kamino','Rattatak','Mustafar','Iego','Geonosis','Felucia','Dantooine','Ansion','Artaru','Bespin','Boz Pity','Cato Neimoidia','Christophsis','Kashyyyk','Kessel','Malastare','Mygeeto','Nar Shaddaa','Ord Mantell','Saleucami','Subterrel','Death Star','Teth','Tund','Utapau','Yavin'];

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

function planets_settings_post($post)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}
	if ($_POST['planets-submit']) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'planets', 'enable' ,intval($_POST['planets']));
	}
}


/**
 *
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 */



function planets_settings(array &$data)
{
	if(!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(),'planets','enable');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/planets/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['planets', DI::l10n()->t('Enable Planets Addon'), $enabled],
	]);

	$data = [
		'addon' => 'planets',
		'title' => DI::l10n()->t('Planets Settings'),
		'html'  => $html,
	];
}
