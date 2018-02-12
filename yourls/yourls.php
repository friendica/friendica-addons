<?php


/**
 * Name: Yourls
 * Description: Defines a YourLS url shortener for the Statusnet & Twitter plugins
 * Version: 1.0
 * Author: Keith Fernie <http://friendika.me4.it/profile/keith>
 * 
 */

function yourls_install() {
	register_hook('plugin_settings', 'addon/yourls/yourls.php', 'yourls_addon_settings');
	register_hook('plugin_settings_post', 'addon/yourls/yourls.php', 'yourls_addon_settings_post');

}


function yourls_uninstall() {
	unregister_hook('plugin_settings', 'addon/yourls/yourls.php', 'yourls_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/yourls/yourls.php', 'yourls_addon_settings_post');
	set_config('yourls','url1',trim($_POST['']));
	set_config('yourls','username1',trim($_POST['']));
	set_config('yourls','password1',trim($_POST['']));
	set_config('yourls','ssl1',trim($_POST['']));

}





function yourls_addon_settings(&$a,&$s) {


	if(! is_site_admin())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/yourls/yourls.css' . '" media="all" />' . "\r\n";


	$yourls_url = get_config('yourls','url1');
	$yourls_username = get_config('yourls','username1');
	$yourls_password = get_config('yourls', 'password1');
	$ssl_enabled = get_config('yourls','ssl1');
	$ssl_checked = (($ssl_enabled) ? ' checked="checked" ' : '');



	$yourls_ssl = get_config('yourls', 'ssl1');

	$s .= '<span id="settings_yourls_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_yourls_expanded\'); openClose(\'settings_yourls_inflated\');">';
	$s .= '<h3>' . t('YourLS') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_yourls_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_yourls_expanded\'); openClose(\'settings_yourls_inflated\');">';
	$s .= '<h3>' . t('YourLS') . '</h3>';
	$s .= '</span>';

	$s .= '<div id="yourls-url-wrapper">';
	$s .= '<label id="yourls-url-label" for="yourls-url">' . t('URL: http://') . '</label>';
	$s .= '<input id="yourls-url" type="text" name="yourls_url" value="' . $yourls_url .'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="yourls-username-wrapper">';
	$s .= '<label id="yourls-username-label" for="yourls-username">' . t('Username:') . '</label>';
	$s .= '<input id="yourls-username" type="text" name="yourls_username" value="' . $yourls_username .'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="yourls-password-wrapper">';
	$s .= '<label id="yourls-password-label" for="yourls-password">' . t('Password:') . '</label>';
	$s .= '<input id="yourls-password" type="password" name="yourls_password" value="' . $yourls_password .'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="yourls-ssl-wrapper">';
	$s .= '<label id="yourls-ssl-label" for="yourls-ssl">' . t('Use SSL ') . '</label>';
	$s .= '<input id="yourls-ssl" type="checkbox" name="yourls_ssl" value="1" ' . $ssl_checked . ' />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="yourls-submit" name="yourls-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

	return;

}

function yourls_addon_settings_post(&$a,&$b) {

	if(! is_site_admin())
		return;

	if($_POST['yourls-submit']) {
		set_config('yourls','url1',trim($_POST['yourls_url']));
		set_config('yourls','username1',trim($_POST['yourls_username']));
		set_config('yourls','password1',trim($_POST['yourls_password']));
		set_config('yourls','ssl1',intval($_POST['yourls_ssl']));
		info( t('yourls Settings saved.') . EOL);
	}
}
