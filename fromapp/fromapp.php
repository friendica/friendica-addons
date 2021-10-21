<?php
/**
 * Name: FromApp
 * Description: Change the displayed application you are posting from
 * Version: 1.0
 * Author: Commander Zot
 *
 */
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\DI;

function fromapp_install()
{
	Hook::register('post_local', 'addon/fromapp/fromapp.php', 'fromapp_post_hook');
	Hook::register('addon_settings', 'addon/fromapp/fromapp.php', 'fromapp_settings');
	Hook::register('addon_settings_post', 'addon/fromapp/fromapp.php', 'fromapp_settings_post');
	Logger::notice("installed fromapp");
}

function fromapp_settings_post($a, $post)
{
	if (!local_user() || empty($_POST['fromapp-submit'])) {
		return;
	}

	DI::pConfig()->set(local_user(), 'fromapp', 'app', $_POST['fromapp-input']);
	DI::pConfig()->set(local_user(), 'fromapp', 'force', intval($_POST['fromapp-force']));
}

function fromapp_settings(&$a, &$s)
{
	if (!local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/fromapp/fromapp.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$fromapp = DI::pConfig()->get(local_user(), 'fromapp', 'app', '');

	$force = intval(DI::pConfig()->get(local_user(), 'fromapp', 'force'));

	$force_enabled = (($force) ? ' checked="checked" ' : '');

	
	/* Add some HTML to the existing form */

	$s .= '<span id="settings_fromapp_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_fromapp_expanded\'); openClose(\'settings_fromapp_inflated\');">';
	$s .= '<h3>' . DI::l10n()->t('FromApp Settings') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_fromapp_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_fromapp_expanded\'); openClose(\'settings_fromapp_inflated\');">';
	$s .= '<h3>' . DI::l10n()->t('FromApp Settings') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="fromapp-wrapper">';
	$s .= '<label id="fromapp-label" for="fromapp-input">' . DI::l10n()->t('The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.') . '</label>';
	$s .= '<input id="fromapp-input" type="text" name="fromapp-input" value="' . $fromapp . '" ' . '/>';
	$s .= '<div class="clear"></div>';

	$s .= '<label id="fromapp-force-label" for="fromapp-force">' . DI::l10n()->t('Use this application name even if another application was used.') . '</label>';
	$s .= '<input id="fromapp-force" type="checkbox" name="fromapp-force" value="1" ' . $force_enabled . '/>';

	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="fromapp-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';
}

function fromapp_post_hook(&$a, &$item)
{
	if (! local_user()) {
		return;
	}

	if (local_user() != $item['uid']) {
		return;
	}

	$app = DI::pConfig()->get(local_user(), 'fromapp', 'app');
	$force = intval(DI::pConfig()->get(local_user(), 'fromapp', 'force'));

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
