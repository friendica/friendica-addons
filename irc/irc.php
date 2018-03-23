<?php
/**
* Name: IRC Chat Addon
* Description: add an Internet Relay Chat chatroom on freenode
* Version: 1.1
* Author: tony baldwin <https://free-haven.org/profile/tony>
* Author: Tobias Diekershoff <https://f.diekershoff.de/u/tobias>
*/
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;

function irc_install() {
	Addon::registerHook('app_menu', 'addon/irc/irc.php', 'irc_app_menu');
	Addon::registerHook('addon_settings', 'addon/irc/irc.php', 'irc_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/irc/irc.php', 'irc_addon_settings_post');
}

function irc_uninstall() {
	Addon::unregisterHook('app_menu', 'addon/irc/irc.php', 'irc_app_menu');
	Addon::unregisterHook('addon_settings', 'addon/irc/irc.php', 'irc_addon_settings');

}


function irc_addon_settings(&$a,&$s) {
	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

//	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/irc/irc.css' . '" media="all" />' . "\r\n";

    /* setting popular channels, auto connect channels */
	$sitechats = get_pconfig( local_user(), 'irc','sitechats'); /* popular channels */
	$autochans = get_pconfig( local_user(), 'irc','autochans');  /* auto connect chans */

	$t = get_markup_template( "settings.tpl", "addon/irc/" );
	$s .= replace_macros($t, [
	    	'$header' => L10n::t('IRC Settings'),
		'$info' => L10n::t('Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'),
		'$submit' => L10n::t('Save Settings'),
		'$autochans' => [ 'autochans', L10n::t('Channel(s) to auto connect (comma separated)'), $autochans, L10n::t('List of channels that shall automatically connected to when the app is launched.')],
		'$sitechats' => [ 'sitechats', L10n::t('Popular Channels (comma separated)'), $sitechats, L10n::t('List of popular channels, will be displayed at the side and hotlinked for easy joining.') ]
	]);


	return;

}

function irc_addon_settings_post(&$a,&$b) {
	if(! local_user())
		return;

	if($_POST['irc-submit']) {
		set_pconfig( local_user(), 'irc','autochans',trim($_POST['autochans']));
		set_pconfig( local_user(), 'irc','sitechats',trim($_POST['sitechats']));
		/* upid pop-up thing */
		info(L10n::t('IRC settings saved.') . EOL);
	}
}

function irc_app_menu($a,&$b) {
	$b['app_menu'][] = '<div class="app-title"><a href="irc">' . L10n::t('IRC Chatroom') . '</a></div>';
}


function irc_module() {
	return;
}


function irc_content(&$a) {

	$baseurl = $a->get_baseurl() . '/addon/irc';
	$o = '';

	/* set the list of popular channels */
	if (local_user()) {
	    $sitechats = get_pconfig( local_user(), 'irc', 'sitechats');
	    if (!$sitechats)
		$sitechats = get_config('irc', 'sitechats');
	} else {
	    $sitechats = get_config('irc','sitechats');
	}
	if($sitechats)
		$chats = explode(',',$sitechats);
	else
		$chats = array('friendica','chat','chatback','hottub','ircbar','dateroom','debian');


	$a->page['aside'] .= '<div class="widget"><h3>' . L10n::t('Popular Channels') . '</h3><ul>';
	foreach($chats as $chat) {
		$a->page['aside'] .= '<li><a href="' . $a->get_baseurl() . '/irc?channels=' . $chat . '" >' . '#' . $chat . '</a></li>';
	}
	$a->page['aside'] .= '</ul></div>';

        /* setting the channel(s) to auto connect */
	if (local_user()) {
	    $autochans = get_pconfig(local_user(), 'irc', 'autochans');
	    if (!$autochans)
		$autochans = get_config('irc','autochans');
	} else {
	    $autochans = get_config('irc','autochans');
	}
	if($autochans)
		$channels = $autochans;
	else
		$channels = ((x($_GET,'channels')) ? $_GET['channels'] : 'friendica');

/* add the chatroom frame and some html */
  $o .= <<< EOT
<h2>IRC chat</h2>
<p><a href="http://tldp.org/HOWTO/IRC/beginners.html" target="_blank">A beginner's guide to using IRC. [en]</a></p>
<iframe src="//webchat.freenode.net?channels=$channels" style="width:100%; max-width:900px; height: 600px;"></iframe>
EOT;

return $o;
    
}

function irc_addon_admin_post (&$a) {
	if(! is_site_admin())
		return;

	if($_POST['irc-submit']) {
		set_config('irc','autochans',trim($_POST['autochans']));
		set_config('irc','sitechats',trim($_POST['sitechats']));
		/* stupid pop-up thing */
		info(L10n::t('IRC settings saved.') . EOL);
	}
}
function irc_addon_admin (&$a, &$o) {
	$sitechats = Config::get('irc','sitechats'); /* popular channels */
	$autochans = Config::get('irc','autochans');  /* auto connect chans */
	$t = get_markup_template( "admin.tpl", "addon/irc/" );
	$o = replace_macros($t, [
		'$submit' => L10n::t('Save Settings'),
		'$autochans' => [ 'autochans', L10n::t('Channel(s) to auto connect (comma separated)'), $autochans, L10n::t('List of channels that shall automatically connected to when the app is launched.')],
		'$sitechats' => [ 'sitechats', L10n::t('Popular Channels (comma separated)'), $sitechats, L10n::t('List of popular channels, will be displayed at the side and hotlinked for easy joining.') ]
	]);
}
