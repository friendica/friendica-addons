<?php
/**
 * Name: FromApp
 * Description: Change the displayed application you are posting from
 * Version: 1.0
 * Author: Commander Zot
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function fromapp_install()
{
	Hook::register('post_local', 'addon/fromapp/fromapp.php', 'fromapp_post_hook');
	Hook::register('addon_settings', 'addon/fromapp/fromapp.php', 'fromapp_settings');
	Hook::register('addon_settings_post', 'addon/fromapp/fromapp.php', 'fromapp_settings_post');
	Logger::notice("installed fromapp");
}

function fromapp_settings_post($post)
{
	if (!DI::userSession()->getLocalUserId() || empty($_POST['fromapp-submit'])) {
		return;
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'fromapp', 'app', $_POST['fromapp-input']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'fromapp', 'force', intval($_POST['fromapp-force']));
}

function fromapp_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$fromapp = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'fromapp', 'app', '');
	$force   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'fromapp', 'force'));

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/fromapp/');
	$html = Renderer::replaceMacros($t, [
		'$fromapp' => ['fromapp-input', DI::l10n()->t('The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'), $fromapp],
		'$force'   => ['fromapp-force', DI::l10n()->t('Use this application name even if another application was used.'), $force],
	]);

	$data = [
		'addon' => 'fromapp',
		'title' => DI::l10n()->t('FromApp Settings'),
		'html'  => $html,
	];
}

function fromapp_post_hook(&$item)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::userSession()->getLocalUserId() != $item['uid']) {
		return;
	}

	$app = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'fromapp', 'app');
	$force = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'fromapp', 'force'));

	if (is_null($app) || (! strlen($app))) {
		return;
	}

	if (strlen(trim($item['app'])) && (! $force)) {
		return;
	}

	$apps = explode(',', $app);
	$item['app'] = trim($apps[mt_rand(0, count($apps)-1)]);
	
	return;
}
