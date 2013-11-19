<?php
/**
* Name: IRC Chat Plugin
* Description: add an Internet Relay Chat chatroom
* Version: 1.0
* Author: tony baldwin <https://free-haven.org/profile/tony>
*/

/* enable in admin->plugins
 * you will then have "irc chatroom" listed at yoursite/apps
 * and the app will run at yoursite/irc
 * documentation at http://tonybaldwin.me/hax/doku.php?id=friendica:irc
 * admin can set popular chans, auto connect chans in settings->plugin settings
 */

function irc_install() {
	register_hook('app_menu', 'addon/irc/irc.php', 'irc_app_menu');
	register_hook('plugin_settings', 'addon/irc/irc.php', 'irc_addon_settings');
	register_hook('plugin_settings_post', 'addon/irc/irc.php', 'irc_addon_settings_post');
}

function irc_uninstall() {
	unregister_hook('app_menu', 'addon/irc/irc.php', 'irc_app_menu');
	unregister_hook('plugin_settings', 'addon/irc/irc.php', 'irc_addon_settings');

}


function irc_addon_settings(&$a,&$s) {


	if(! is_site_admin())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/irc/irc.css' . '" media="all" />' . "\r\n";

    /* setting popular channels, auto connect channels */
	$sitechats = get_config('irc','sitechats'); /* popular channels */
	$autochans = get_config('irc','autochans');  /* auto connect chans */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('IRC Settings') . '</h3>';
	$s .= '<div id="irc-chans">';
	$s .= '<label id="irc-auto-label" for="autochans">' . t('Channel(s) to auto connect (comma separated)') . '</label>';
	$s .= '<input id="irc-autochans" type="text" name="autochans" value="' . $autochans .'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="irc-popchans">';
	$s .= '<label id="irc-pop-label" for="sitechats">' . t('Popular Channels (comma separated)') . '</label>';
	$s .= '<input id="irc-sitechats" type="text" name="sitechats" value="' . $sitechats.'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="irc-submit" name="irc-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

	return;

}

function irc_addon_settings_post(&$a,&$b) {
	if(! is_site_admin())
		return;

	if($_POST['irc-submit']) {
		set_config('irc','autochans',trim($_POST['autochans']));
		set_config('irc','sitechats',trim($_POST['sitechats']));
		/* stupid pop-up thing */
		info( t('IRC settings saved.') . EOL);
	}
}

function irc_app_menu($a,&$b) {
$b['app_menu'][] = '<div class="app-title"><a href="irc">' . t('IRC Chatroom') . '</a></div>';
}


function irc_module() {
return;
}


function irc_content(&$a) {

	$baseurl = $a->get_baseurl() . '/addon/irc';
	$o = '';

	/* set the list of popular channels */
	$sitechats = get_config('irc','sitechats');
	if($sitechats)
		$chats = explode(',',$sitechats);
	else
		$chats = array('friendica','chat','chatback','hottub','ircbar','dateroom','debian');


	$a->page['aside'] .= '<div class="widget"><h3>' . t('Popular Channels') . '</h3><ul>';
	foreach($chats as $chat) {
		$a->page['aside'] .= '<li><a href="' . $a->get_baseurl() . '/irc?channels=' . $chat . '" >' . '#' . $chat . '</a></li>';
	}
	$a->page['aside'] .= '</ul></div>';

        /* setting the channel(s) to auto connect */
	$autochans = get_config('irc','autochans');
	if($autochans)
		$channels = $autochans;
	else
		$channels = ((x($_GET,'channels')) ? $_GET['channels'] : 'friendica');

/* add the chatroom frame and some html */
  $o .= <<< EOT
<h2>IRC chat</h2>
<p><a href="http://tldp.org/HOWTO/IRC/beginners.html" target="_blank">A beginner's guide to using IRC. [en]</a></p>
<iframe src="//webchat.freenode.net?channels=$channels" width="600" height="600"></iframe>
EOT;

return $o;
    
}


