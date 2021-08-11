<?php
/**
* Name: IRC Chat Addon
* Description: add an Internet Relay Chat chatroom on freenode
* Version: 1.1
* Author: tony baldwin <https://free-haven.org/profile/tony>
* Author: Tobias Diekershoff <https://f.diekershoff.de/u/tobias>
*/

use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function irc_install() {
	Hook::register('app_menu', 'addon/irc/irc.php', 'irc_app_menu');
	Hook::register('addon_settings', 'addon/irc/irc.php', 'irc_addon_settings');
	Hook::register('addon_settings_post', 'addon/irc/irc.php', 'irc_addon_settings_post');
}

function irc_addon_settings(&$a,&$s) {
	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

//	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/irc/irc.css' . '" media="all" />' . "\r\n";

    /* setting popular channels, auto connect channels */
	$sitechats = DI::pConfig()->get( local_user(), 'irc','sitechats'); /* popular channels */
	$autochans = DI::pConfig()->get( local_user(), 'irc','autochans');  /* auto connect chans */

	$t = Renderer::getMarkupTemplate( "settings.tpl", "addon/irc/" );
	$s .= Renderer::replaceMacros($t, [
	    	'$header' => DI::l10n()->t('IRC Settings'),
		'$info' => DI::l10n()->t('Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'),
		'$submit' => DI::l10n()->t('Save Settings'),
		'$autochans' => [ 'autochans', DI::l10n()->t('Channel(s) to auto connect (comma separated)'), $autochans, DI::l10n()->t('List of channels that shall automatically connected to when the app is launched.')],
		'$sitechats' => [ 'sitechats', DI::l10n()->t('Popular Channels (comma separated)'), $sitechats, DI::l10n()->t('List of popular channels, will be displayed at the side and hotlinked for easy joining.') ]
	]);


	return;

}

function irc_addon_settings_post(&$a, &$b) {
	if(!local_user())
		return;

	if(!empty($_POST['irc-submit'])) {
		if (isset($_POST['autochans'])) {
			DI::pConfig()->set(local_user(), 'irc', 'autochans', trim(($_POST['autochans'])));
		}
		if (isset($_POST['sitechats'])) {
			DI::pConfig()->set(local_user(), 'irc', 'sitechats', trim($_POST['sitechats']));
		}
		/* upid pop-up thing */
	}
}

function irc_app_menu($a,&$b) {
	$b['app_menu'][] = '<div class="app-title"><a href="irc">' . DI::l10n()->t('IRC Chatroom') . '</a></div>';
}


function irc_module() {
	return;
}


function irc_content(&$a) {

	$baseurl = DI::baseUrl()->get() . '/addon/irc';
	$o = '';

	/* set the list of popular channels */
	if (local_user()) {
	    $sitechats = DI::pConfig()->get( local_user(), 'irc', 'sitechats');
	    if (!$sitechats)
		$sitechats = DI::config()->get('irc', 'sitechats');
	} else {
	    $sitechats = DI::config()->get('irc','sitechats');
	}
	if($sitechats)
		$chats = explode(',',$sitechats);
	else
		$chats = ['friendica','chat','chatback','hottub','ircbar','dateroom','debian'];


	DI::page()['aside'] .= '<div class="widget"><h3>' . DI::l10n()->t('Popular Channels') . '</h3><ul>';
	foreach($chats as $chat) {
		DI::page()['aside'] .= '<li><a href="' . DI::baseUrl()->get() . '/irc?channels=' . $chat . '" >' . '#' . $chat . '</a></li>';
	}
	DI::page()['aside'] .= '</ul></div>';

        /* setting the channel(s) to auto connect */
	if (local_user()) {
	    $autochans = DI::pConfig()->get(local_user(), 'irc', 'autochans');
	    if (!$autochans)
		$autochans = DI::config()->get('irc','autochans');
	} else {
	    $autochans = DI::config()->get('irc','autochans');
	}
	if($autochans)
		$channels = $autochans;
	else
		$channels = ($_GET['channels'] ?? '') ?: 'friendica';

/* add the chatroom frame and some html */
  $o .= <<< EOT
<h2>IRC chat</h2>
<p><a href="https://tldp.org/HOWTO/IRC/beginners.html" target="_blank" rel="noopener noreferrer">A beginner's guide to using IRC. [en]</a></p>
<iframe src="//web.libera.chat?channels=$channels" style="width:100%; max-width:900px; height: 600px;"></iframe>
EOT;

return $o;

}

function irc_addon_admin_post (&$a) {
	if(! is_site_admin())
		return;

	if($_POST['irc-submit']) {
		DI::config()->set('irc','autochans',trim($_POST['autochans']));
		DI::config()->set('irc','sitechats',trim($_POST['sitechats']));
	}
}
function irc_addon_admin (&$a, &$o) {
	$sitechats = DI::config()->get('irc','sitechats'); /* popular channels */
	$autochans = DI::config()->get('irc','autochans');  /* auto connect chans */
	$t = Renderer::getMarkupTemplate( "admin.tpl", "addon/irc/" );
	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$autochans' => [ 'autochans', DI::l10n()->t('Channel(s) to auto connect (comma separated)'), $autochans, DI::l10n()->t('List of channels that shall automatically connected to when the app is launched.')],
		'$sitechats' => [ 'sitechats', DI::l10n()->t('Popular Channels (comma separated)'), $sitechats, DI::l10n()->t('List of popular channels, will be displayed at the side and hotlinked for easy joining.') ]
	]);
}
