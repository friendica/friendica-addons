<?php
/**
 * Name: Notimeline
 * Description: Disable "Archives" widget on profile page
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Status: Unsupported
 *
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\DI;

function notimeline_install()
{
	Hook::register('addon_settings', 'addon/notimeline/notimeline.php', 'notimeline_settings');
	Hook::register('addon_settings_post', 'addon/notimeline/notimeline.php', 'notimeline_settings_post');
}

function notimeline_uninstall()
{
	Hook::unregister('addon_settings', 'addon/notimeline/notimeline.php', 'notimeline_settings');
	Hook::unregister('addon_settings_post', 'addon/notimeline/notimeline.php', 'notimeline_settings_post');
}

function notimeline_settings_post($a, $post)
{
	if (!local_user() || empty($_POST['notimeline-submit'])) {
		return;
	}

	DI::pConfig()->set(local_user(), 'system', 'no_wall_archive_widget', intval($_POST['notimeline']));
	info(DI::l10n()->t('No Timeline settings updated.') . EOL);
}

function notimeline_settings(&$a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/notimeline/notimeline.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$notimeline = DI::pConfig()->get(local_user(), 'system', 'no_wall_archive_widget', false);

	$notimeline_checked = (($notimeline) ? ' checked="checked" ' : '');

	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . DI::l10n()->t('No Timeline Settings') . '</h3>';
	$s .= '<div id="notimeline-wrapper">';
	$s .= '<label id="notimeline-label" for="notimeline-checkbox">' . DI::l10n()->t('Disable Archive selector on profile wall') . '</label>';
	$s .= '<input id="notimeline-checkbox" type="checkbox" name="notimeline" value="1" ' . $notimeline_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="notimeline-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';
}
