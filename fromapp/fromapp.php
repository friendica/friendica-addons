<?php
/**
 * Name: FromApp
 * Description: Change the displayed application you are posting from
 * Version: 1.0
 * Author: Commander Zot
 *
 */


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

	set_pconfig(local_user(),'fromapp','app',$_POST['fromapp']);
	info( t('Fromapp settings updated.') . EOL);
}

function fromapp_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/fromapp/fromapp.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$fromapp = get_pconfig(local_user(),'fromapp','app');
	if($fromapp === false)
		$fromapp = '';
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('FromApp Settings') . '</h3>';
	$s .= '<div id="fromapp-wrapper">';
	$s .= '<label id="fromapp-label" for="fromapp">' . t('The application name you would like to show your posts originating from.') . '</label>';
	$s .= '<input id="fromapp-input" type="text" name="fromapp" value="' . $fromapp . '" ' . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="fromapp-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}

function fromapp_post_hook(&$a,&$item) {
   if(! local_user())
        return;

    if(local_user() != $item['uid'])
        return;

    $app = get_pconfig(local_user(), 'fromapp', 'app');

    if(($app === false) || (! strlen($app)))
        return;

	$apps = explode(',',$app);
	$item['app'] = trim($apps[mt_rand(0,count($apps)-1)]);
	return;

}