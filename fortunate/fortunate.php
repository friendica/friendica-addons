<?php
/**
 * Name: Fortunate
 * Description: Add a random fortune cookie at the bottom of every pages. [Requires manual confguration.]
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */
use Friendica\Core\Addon;

// IMPORTANT: SET THIS to your fortunate server

define ('FORTUNATE_SERVER', 'hostname.com');

function fortunate_install() {
	Addon::registerHook('page_end', 'addon/fortunate/fortunate.php', 'fortunate_fetch');
	if(FORTUNATE_SERVER == 'hostname.com' && is_site_admin()) {
		notice('Fortunate plugin requires configuration. See README');
	}
}

function fortunate_uninstall() {
	Addon::unregisterHook('page_end', 'addon/fortunate/fortunate.php', 'fortunate_fetch');
}


function fortunate_fetch(&$a,&$b) {

	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="' 
		. $a->get_baseurl() . '/addon/fortunate/fortunate.css' . '" media="all" />' . "\r\n";

	if(FORTUNATE_SERVER != 'hostname.com') {
		$s = fetch_url('http://' . FORTUNATE_SERVER . '/cookie.php?numlines=2&equal=1&rand=' . mt_rand());
		$b .= '<div class="fortunate">' . $s . '</div>';
	}
}

