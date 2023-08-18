<?php
/*
 * Name: Audon Application
 * Description: add a Audon instance. Based on webRTC Addon
 * Version: 0.1
 * Author: Stephen Mahood <https://friends.mayfirst.org/profile/marxistvegan>
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function audon_install()
{
	Hook::register('app_menu', __FILE__, 'audon_app_menu');
}

function audon_app_menu(array &$b)
{
	$b['app_menu'][] = '<div class="app-title"><a href="audon">' . DI::l10n()->t('Audon Audiochat') . '</a></div>';
}

function audon_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/audon/');
	$o = Renderer::replaceMacros($t, [
		'$submit'   => DI::l10n()->t('Save Settings'), 
		'$audonurl' => [
			'audonurl', 
			DI::l10n()->t('Audon Base URL'), 
			DI::config()->get('audon','audonurl'), 
			DI::l10n()->t('Page your users will create an Audon audio chat room on. For example you could use https://audon.space.'), 
		], 
	]);
}

function audon_addon_admin_post()
{
	DI::config()->set('audon', 'audonurl', trim($_POST['audonurl'] ?? ''));
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function audon_module() {}

function audon_content(): string
{
	$o = '';

	/* landingpage to create chatrooms */
	$audonurl = DI::config()->get('audon', 'audonurl');


	/* embedd the landing page in an iframe */
	$o .= '<h2>' . DI::l10n()->t('Audio Chat') . '</h2>';
	$o .= '<p>' . DI::l10n()->t('Audon is an audio conferencing tool. Connect your account to Audon and create a room. Share the generated link to talk to other participants.') . '</p>';
	if ($audonurl == '') {
		$o .= '<p>' . DI::l10n()->t('Please contact your Friendica administrator to remind them to configure the Audon addon.') . '</p>';
	} else {
		$o .= '<iframe src="' . $audonurl . '" width="740px" height="600px"></iframe>';
	}

	return $o;
}
