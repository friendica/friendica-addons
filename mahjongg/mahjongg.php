<?php

/**
 * Name: Mah Jongg
 * Description: Ancient Chinese puzzle game that never gets old.
 * Version: 1.0
 * Author: Holger Froese
 */
use Friendica\Core\Addon;

function mahjongg_install() {
    Addon::registerHook('app_menu', 'addon/mahjongg/mahjongg.php', 'mahjongg_app_menu');
}

function mahjongg_uninstall() {
    Addon::unregisterHook('app_menu', 'addon/mahjongg/mahjongg.php', 'mahjongg_app_menu');

}

function mahjongg_app_menu($a,&$b) {
    $b['app_menu'][] = '<div class="app-title"><a href="mahjongg">Mahjongg</a></div>';
}


function mahjongg_module() {}

function mahjongg_content(&$a) {

$baseurl = $a->get_baseurl() . '/addon/mahjongg';

$o .= <<< EOT
<br><br>
<p align="left">
<embed src="addon/mahjongg/mahjongg.swf" quality="high" bgcolor="#FFFFFF" width="800" height="600" name="mahjongg" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
<br><br>
<b>Simply locate the matching tiles and find a way to clear them from the board as quickly as possible.
A timer at the top of the screen keeps track of how you are doing.</b><br>
</p>
EOT;

return $o;
}
