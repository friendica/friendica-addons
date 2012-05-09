<?php
/**
* Name: Hangman Plugin
* Description: spell words, hang dudes
* Version: 1.0
* Author: tony baldwin <https://free-haven.org/profile/tony>
*/

function hangman_install() {
register_hook('app_menu', 'addon/hangman/hangman.php', 'hangman_app_menu');
}

function hangman_uninstall() {
unregister_hook('hangman_menu', 'addon/hangman/hangman.php', 'hangman_app_menu');

}

function hangman_app_menu($a,&$b) {
$b['app_menu'][] = '<div class="app-title"><a href="hangman">' . t('Hangman') . '</a></div>';
}


function hangman_module() {
return;
}


function hangman_content(&$a) {

$baseurl = $a->get_baseurl() . '/addon/hangman';
$a->page['htmlhead'] .= '<link rel="stylesheet" href="' .$a->get_baseurl() . '/addon/hangman/hang.css' . '" type="text/css" />' . "\r\n";
$a->page['htmlhead'] .= '<script src="' .$a->get_baseurl() . '/addon/hangman/hangans.js' .'" type="text/javascript"> </script>' . "\r\n";
$o = '';



  $o .= <<< EOT

<script src="$baseurl/hangman.js" type="text/javascript">
</script><noscript><div align="center"><b>The Hangman
game requires Javascript</b><br />You either have
Javascript disabled<br />or the browser you are using does
not<br />support Javascript. Please use a Javascript
<br />enabled browser to access this game.</div></noscript> 

EOT;
return $o;
    
}
