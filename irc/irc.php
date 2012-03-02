<?php
/**
* Name: IRC Chat Plugin
* Description: add an Internet Relay Chat chatroom
* Version: 1.0
* Author: tony baldwin <http://tonybaldwin.me>
*/


function irc_install() {
register_hook('app_menu', 'addon/irc/irc.php', 'irc_app_menu');
}

function irc_uninstall() {
unregister_hook('app_menu', 'addon/irc/irc.php', 'irc_app_menu');

}

function irc_app_menu($a,&$b) {
$b['app_menu'][] = '<div class="app-title"><a href="irc">' . t('irc Chatroom') . '</a></div>';
}


function irc_module() {
return;
}


function irc_content(&$a) {

$baseurl = $a->get_baseurl() . '/addon/irc';
$o = '';


 // add the chatroom frame and some html
  $o .= <<< EOT
<h2>IRC chat</h2>
<iframe src="http://webchat.freenode.net?channels=friendica" width="600" height="600"></iframe>
EOT;

return $o;
    
}


