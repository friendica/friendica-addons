<?php
/**
 * Name: Circle Text
 * Description: Disable images in circle edit menu
 * Version: 1.0
 * Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function circle_text_install()
{
	Hook::register('addon_settings', __FILE__, 'circle_text_settings');
	Hook::register('addon_settings_post', __FILE__, 'circle_text_settings_post');
}

/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function circle_text_settings_post(array $post)
{
	if (!DI::userSession()->getLocalUserId() || empty($post['circle_text-submit'])) {
		return;
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'system', 'circle_edit_image_limit', intval($post['circle_text']));
}


/**
 *
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 */

function circle_text_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'system', 'circle_edit_image_limit') ??
		DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'system', 'groupedit_image_limit');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/circle_text/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['circle_text', DI::l10n()->t('Use a text only (non-image) circle selector in the "circle edit" menu'), $enabled],
	]);

	$data = [
		'addon' => 'circle_text',
		'title' => DI::l10n()->t('Circle Text'),
		'html'  => $html,
	];
}
