<?php
/**
 * Name: FromApp
 * Description: Change the displayed application you are posting from
 * Version: 1.0
 * Author: Commander Zot
 *
 */

use Friendica\Core\PConfig;

function fromapp_install() {

	register_hook('post_local', 'addon/fromapp/fromapp.php', 'fromapp_post_hook');
	register_hook('plugin_settings', 'addon/fromapp/fromapp.php', 'fromapp_settings');
	register_hook('plugin_settings_post', 'addon/fromapp/fromapp.php', 'fromapp_settings_post');

	logger("installed fromapp");
}


function fromapp_uninstall() {

	unregister_hook('post_local', 'addon/fromapp/fromapp.php', 'fromapp_post_hook');
	unregister_hook('plugin_settings', 'addon/fromapp/fromapp.php', 'fromapp_settings');
	unregister_hook('plugin_settings_post', 'addon/fromapp/fromapp.php', 'fromapp_settings_post');


	logger("removed fromapp");
}

function fromapp_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'fromapp-submit')))
		return;

	PConfig::set(local_user(),'fromapp','app',$_POST['fromapp-input']);
	PConfig::set(local_user(),'fromapp','force',intval($_POST['fromapp-force']));

	info( t('Fromapp settings updated.') . EOL);
}

function fromapp_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/fromapp/fromapp.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$fromapp = PConfig::get(local_user(),'fromapp','app');
	if(is_null($fromapp))
		$fromapp = '';

	$force = intval(PConfig::get(local_user(),'fromapp','force'));

	$force_enabled = (($force) ? ' checked="checked" ' : '');

	
	/* Add some HTML to the existing form */

	$s .= '<span id="settings_fromapp_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_fromapp_expanded\'); openClose(\'settings_fromapp_inflated\');">';
	$s .= '<h3>' . t('FromApp Settings') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_fromapp_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_fromapp_expanded\'); openClose(\'settings_fromapp_inflated\');">';
	$s .= '<h3>' . t('FromApp Settings') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="fromapp-wrapper">';
	$s .= '<label id="fromapp-label" for="fromapp-input">' . t('The application name you would like to show your posts originating from.') . '</label>';
	$s .= '<input id="fromapp-input" type="text" name="fromapp-input" value="' . $fromapp . '" ' . '/>';
	$s .= '<div class="clear"></div>';

	$s .= '<label id="fromapp-force-label" for="fromapp-force">' . t('Use this application name even if another application was used.') . '</label>';
	$s .= '<input id="fromapp-force" type="checkbox" name="fromapp-force" value="1" ' . $force_enabled . '/>';

	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="fromapp-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}

function fromapp_post_hook(&$a,&$item)
{
	if(! local_user()) {
		return;
	}

    if(local_user() != $item['uid']) {
		return;
	}

    $app = PConfig::get(local_user(), 'fromapp', 'app');
	$force = intval(PConfig::get(local_user(), 'fromapp','force'));

    if(is_null($app) || (! strlen($app))) {
		return;
	}

	if(strlen(trim($item['app'])) && (! $force)) {
		return;
	}

	$apps = explode(',',$app);
	$item['app'] = trim($apps[mt_rand(0,count($apps)-1)]);
	
	return;
}
