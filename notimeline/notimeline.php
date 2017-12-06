<?php
/**
 * Name: Notimeline
 * Description: Disable "Archives" widget on profile page
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 *
 */

use Friendica\Core\PConfig;

function notimeline_install() {

	register_hook('plugin_settings', 'addon/notimeline/notimeline.php', 'notimeline_settings');
	register_hook('plugin_settings_post', 'addon/notimeline/notimeline.php', 'notimeline_settings_post');

}


function notimeline_uninstall() {
	unregister_hook('plugin_settings', 'addon/notimeline/notimeline.php', 'notimeline_settings');
	unregister_hook('plugin_settings_post', 'addon/notimeline/notimeline.php', 'notimeline_settings_post');

}


function notimeline_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'notimeline-submit')))
		return;

	PConfig::set(local_user(),'system','no_wall_archive_widget',intval($_POST['notimeline']));
	info( t('No Timeline settings updated.') . EOL);
}

function notimeline_settings(&$a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/notimeline/notimeline.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$notimeline = PConfig::get(local_user(), 'system', 'no_wall_archive_widget', false);

	$notimeline_checked = (($notimeline) ? ' checked="checked" ' : '');

	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('No Timeline Settings') . '</h3>';
	$s .= '<div id="notimeline-wrapper">';
	$s .= '<label id="notimeline-label" for="notimeline-checkbox">' . t('Disable Archive selector on profile wall') . '</label>';
	$s .= '<input id="notimeline-checkbox" type="checkbox" name="notimeline" value="1" ' . $notimeline_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="notimeline-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';
}
