<?php
/**
 * Name: Numfriends
 * Description: Change number of contacts shown of profile sidebar
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function numfriends_install() {

	Hook::register('addon_settings', 'addon/numfriends/numfriends.php', 'numfriends_settings');
	Hook::register('addon_settings_post', 'addon/numfriends/numfriends.php', 'numfriends_settings_post');

	Logger::notice("installed numfriends");
}

/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */
function numfriends_settings_post($post) {
	if (! DI::userSession()->getLocalUserId() || empty($_POST['numfriends-submit'])) {
		return;
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'system', 'display_friend_count', intval($_POST['numfriends']));
}


/**
 *
 * Called from the Addon Setting form. 
 * Add our own settings info to the page.
 *
 */
function numfriends_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$numfriends = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'system', 'display_friend_count', 24);
	
	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/numfriends/');
	$html = Renderer::replaceMacros($t, [
		'$numfriends' => ['numfriends', DI::l10n()->t('How many contacts to display on profile sidebar'), $numfriends],
	]);

	$data = [
		'addon' => 'numfriends',
		'title' => DI::l10n()->t('Numfriends Settings'),
		'html'  => $html,
	];
}
