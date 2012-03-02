<?php
/**
 * Name: Mibbit Chat Plugin
 * Description: add a mibbit/irc chatroom
 * Version: 1.0
 * Author: tony baldwin <http://tonybaldwin.me>
 */


function mibbit_install() {
	register_hook('app_menu', 'addon/mibbit/mibbit.php', 'mibbit_app_menu');
}

function mibbit_uninstall() {
	unregister_hook('app_menu', 'addon/mibbit/mibbit.php', 'mibbit_app_menu');

}

function mibbit_app_menu($a,&$b) {
	$b['app_menu'][] = '<div class="app-title"><a href="mibbit">' . t('Mibbit IRC Chatroom') . '</a></div>'; 
}


function mibbit_module() {
	return;
}





function mibbit_content(&$a) {

	$baseurl = $a->get_baseurl() . '/addon/mibbit';
	$o = '';

// this stuff is supposed to go in the page header

$a->page['htmlhead'] .= 'session_start();  
 $nick   = empty($_SESSION[\'user_name\']) ? \'Wdg\' : $_SESSION[\'user_name\'];
 $server = \"irc.mibbit.net\"; // default: 
 $room   = \"friendica\"; // w/o # or %23 !
 
 $uri = \"https://widget.mibbit.com/\" .
 \"?nick=$nick_%3F%3F\" . // each %3F(=?) will be replaced by a random digit 
 \"&customprompt=Welcome%20to%20$server/$room\" .
 \"&customloading=maybe%20you%20need%20to%20close%20other%20Mibbit%20windows%20first...\" .
 \"&settings=c76462e5055bace06e32d325963b39f2\"; // etc.
 if (!empty($room))    {$uri .= \'&channel=%23\' . $room;}  
 if (!empty($server )) {$uri .= \'&server=\'     . $server;}'
 
 // add the chatroom frame and some html
 
    $o .= '<h2>chat</h2>';
    $o .= '<center>';
    $o .= '<iframe  src=\"<?PHP echo $uri; ?>\" frameborder=\"0\">
 [Your user agent does not support frames or is currently configured
 not to display frames. However, you may want to open the
 <A href=\"<?PHP echo $uri; ?>\" target=\"_blank\"> chat in a new browser window ...</A>]';
    $o .= '</iframe>';
    $o .= '<br>(no spaces, interpunctuation or leading ciphers in your /nick name)';
    $o .= '<h4>type /help to learn about special commands</h4>';
    $o .= '</center><hr>'
    
}
