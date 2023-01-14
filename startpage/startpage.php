<?php
/**
 * Name: Start Page
 * Description: Set a preferred page to load on login from home page
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function startpage_install() {
	Hook::register('home_init', 'addon/startpage/startpage.php', 'startpage_home_init');
	Hook::register('addon_settings', 'addon/startpage/startpage.php', 'startpage_settings');
	Hook::register('addon_settings_post', 'addon/startpage/startpage.php', 'startpage_settings_post');
}

function startpage_home_init($b)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$page = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'startpage', 'startpage');
	if ($page) {
		DI::baseUrl()->redirect($page);
	}
}

/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function startpage_settings_post($post)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (!empty($_POST['startpage-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'startpage', 'startpage', strip_tags(trim($_POST['startpage'])));
	}
}

/**
 *
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 */
function startpage_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$startpage = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'startpage', 'startpage');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/startpage/');
	$html = Renderer::replaceMacros($t, [
		'$startpage' => ['startpage', DI::l10n()->t('Home page to load after login  - leave blank for profile wall'), $startpage, DI::l10n()->t('Examples: "network" or "notifications/system"')],
	]);

	$data = [
		'addon' => 'startpage',
		'title' => DI::l10n()->t('Startpage'),
		'html'  => $html,
	];
}
