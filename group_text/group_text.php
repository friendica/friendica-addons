<?php
/**
 * Name: Group Text
 * Description: Disable images in group edit menu
 * Version: 1.0
 * Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function group_text_install() {

	Hook::register('addon_settings', 'addon/group_text/group_text.php', 'group_text_settings');
	Hook::register('addon_settings_post', 'addon/group_text/group_text.php', 'group_text_settings_post');

	Logger::notice("installed group_text");
}

/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function group_text_settings_post($a,$post) {
	if(! local_user() || empty($_POST['group_text-submit']))
		return;
	DI::pConfig()->set(local_user(),'system','groupedit_image_limit',intval($_POST['group_text']));
}


/**
 *
 * Called from the Addon Setting form. 
 * Add our own settings info to the page.
 *
 */



function group_text_settings(App &$a, array &$data)
{
	if (!local_user()) {
		return;
	}

	$enabled = DI::pConfig()->get(local_user(),'system','groupedit_image_limit');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/group_text/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['group_text', DI::l10n()->t('Use a text only (non-image) group selector in the "group edit" menu'), $enabled],
	]);

	$data = [
		'addon' => 'group_text',
		'title' => DI::l10n()->t('Group Text'),
		'html'  => $html,
	];
}
