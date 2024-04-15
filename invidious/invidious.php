<?php
/*
 * Name: invidious
 * Description: Replaces links to youtube.com to an invidious instance in all displays of postings on a node.
 * Version: 0.4
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 * Status: Unsupported
 * Note: Please use the URL Replace addon instead
 */

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

CONST INVIDIOUS_DEFAULT = 'https://invidio.us';

function invidious_install()
{
	Hook::register('prepare_body_final',  __FILE__, 'invidious_render');
	Hook::register('addon_settings',      __FILE__, 'invidious_settings');
	Hook::register('addon_settings_post', __FILE__, 'invidious_settings_post');
}

/* Handle the send data from the admin settings
 */
function invidious_addon_admin_post()
{
	DI::config()->set('invidious', 'server', trim($_POST['invidiousserver'], " \n\r\t\v\x00/"));
}

/* Hook into the admin settings to let the admin choose an
 * invidious server to use for the replacement.
 */
function invidious_addon_admin(string &$o)
{
	$invidiousserver = DI::config()->get('invidious', 'server', INVIDIOUS_DEFAULT);
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/invidious/');
	$o = Renderer::replaceMacros($t, [
		'$settingdescription' => DI::l10n()->t('Which Invidious server shall be used for the replacements in the post bodies? Use the URL with servername and protocol. See %s for a list of available public Invidious servers.', 'https://redirect.invidious.io'),
		'$invidiousserver'    => ['invidiousserver', DI::l10n()->t('Invidious server'), $invidiousserver, DI::l10n()->t('See %s for a list of available Invidious servers.', '<a href="https://api.invidious.io/">https://api.invidious.io/</a>')],
		'$submit'             => DI::l10n()->t('Save Settings'),
	]);
}

function invidious_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'invidious', 'enabled');
	$server  = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'invidious', 'server', DI::config()->get('invidious', 'server', INVIDIOUS_DEFAULT));

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/invidious/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['enabled', DI::l10n()->t('Replace Youtube links with links to an Invidious server'), $enabled, DI::l10n()->t('If enabled, Youtube links are replaced with the links to the specified Invidious server.')],
		'$server'  => ['server', DI::l10n()->t('Invidious server'), $server, DI::l10n()->t('See %s for a list of available Invidious servers.', '<a href="https://api.invidious.io/">https://api.invidious.io/</a>')],
	]);

	$data = [
		'addon' => 'invidious',
		'title' => DI::l10n()->t('Invidious Settings'),
		'html'  => $html,
	];
}

function invidious_settings_post(array &$b)
{
	if (!DI::userSession()->getLocalUserId() || empty($_POST['invidious-submit'])) {
		return;
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'invidious', 'enabled', (bool)$_POST['enabled']);

	$server = trim($_POST['server'], " \n\r\t\v\x00/");
	if ($server != DI::config()->get('invidious', 'server', INVIDIOUS_DEFAULT) && !empty($server)) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'invidious', 'server', $server);
	} else {
		DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'invidious', 'server');
	}
}

/*
 *  replace "youtube.com" with the chosen Invidious instance
 */
function invidious_render(array &$b)
{
	if (!DI::userSession()->getLocalUserId() || !DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'invidious', 'enabled')) {
		return;
	}

	$original = $b['html'];
	$server   = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'invidious', 'server', DI::config()->get('invidious', 'server', INVIDIOUS_DEFAULT));

	$b['html'] = preg_replace("~https?://(?:www\.)?youtube\.com/watch\?v=(.*?)~ism", $server . '/watch?v=$1', $b['html']);
	$b['html'] = preg_replace("~https?://(?:www\.)?youtube\.com/embed/(.*?)~ism", $server . '/embed/$1', $b['html']);
	$b['html'] = preg_replace("~https?://(?:www\.)?youtube\.com/shorts/(.*?)~ism", $server . '/shorts/$1', $b['html']);
	$b['html'] = preg_replace("/https?:\/\/youtu.be\/(.*?)/ism", $server . '/watch?v=$1', $b['html']);

	if ($original != $b['html']) {
		$b['html'] .= '<hr><p><small>' . DI::l10n()->t('(Invidious addon enabled: YouTube links via %s)', $server) . '</small></p>';
	}
}
