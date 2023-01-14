<?php
/**
 * Name: Gnot
 * Description: Thread email comment notifications on Gmail and anonymise them
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Model\Notification;

function gnot_install()
{
	Hook::register('addon_settings', 'addon/gnot/gnot.php', 'gnot_settings');
	Hook::register('addon_settings_post', 'addon/gnot/gnot.php', 'gnot_settings_post');
	Hook::register('enotify_mail', 'addon/gnot/gnot.php', 'gnot_enotify_mail');

	Logger::notice("installed gnot");
}

/**
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 */
function gnot_settings_post($post) {
	if(! DI::userSession()->getLocalUserId() || empty($_POST['gnot-submit']))
		return;

	DI::pConfig()->set(DI::userSession()->getLocalUserId(),'gnot','enable',intval($_POST['gnot']));
}

/**
 * Called from the Addon Setting form. 
 * Add our own settings info to the page.
 */
function gnot_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$gnot = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'gnot', 'enable'));

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/gnot/');
	$html = Renderer::replaceMacros($t, [
		'$text'    => DI::l10n()->t("Allows threading of email comment notifications on Gmail and anonymising the subject line."),
		'$enabled' => ['gnot', DI::l10n()->t('Enable this addon?'), $gnot],
	]);

	$data = [
		'addon' => 'gnot',
		'title' => DI::l10n()->t('Gnot Settings'),
		'html'  => $html,
	];
}

function gnot_enotify_mail(array &$b)
{
	if ((!$b['uid']) || (! intval(DI::pConfig()->get($b['uid'], 'gnot','enable')))) {
		return;
	}

	if ($b['type'] == Notification\Type::COMMENT) {
		$b['subject'] = DI::l10n()->t('[Friendica:Notify] Comment to conversation #%d', $b['parent']);
	}
}
