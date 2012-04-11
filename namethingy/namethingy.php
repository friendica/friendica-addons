<?php

/**
 *
 * Name: NameThingy
 * Description: The Ultimate Random Name Generator
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */


function namethingy_install() {
    register_hook('app_menu', 'addon/namethingy/namethingy.php', 'namethingy_app_menu');
}

function namethingy_uninstall() {
    unregister_hook('app_menu', 'addon/namethingy/namethingy.php', 'namethingy_app_menu');

}

function namethingy_app_menu($a,&$b) {
    $b['app_menu'][] = '<div class="app-title"><a href="namethingy">NameThingy</a></div>';
}


function namethingy_module() {}

function namethingy_content(&$a) {

$baseurl = $a->get_baseurl() . '/addon/namethingy';

$o .= <<< EOT
<iframe src="http://namethingy.com" width="900" height="700" />
EOT;

return $o;
}
