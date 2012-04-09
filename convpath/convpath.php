<?php
/**
 * Name: Convert Paths
 * Description: Converts all internal paths according to the current scheme (http or https)
 * Version: 1.0
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 * 
 */

function convpath_install() {
	register_hook('page_end', 'addon/convpath/convpath.php', 'convpath_page_end');
	register_hook('page_header', 'addon/convpath/convpath.php', 'convpath_page_header');
}


function convpath_uninstall() {
	unregister_hook('page_end', 'addon/convpath/convpath.php', 'convpath_page_end');
	unregister_hook('page_header', 'addon/convpath/convpath.php', 'convpath_page_header');
}

function convpath_page_header(&$a, &$o){
	$o = convpath_convert($o);
}

function convpath_page_end(&$a, &$o){
	$o = convpath_convert($o);
	$a->page['aside'] = convpath_convert($a->page['aside']);
}

/*
Converts a given path according to the current scheme
*/
function convpath_convert($path) {
	global $a;

	if ($path == "")
		return("");

	$ssl = (substr($a->get_baseurl(), 0, 8) == "https://");

	if ($ssl) {
		$search = "http://".$a->get_hostname();
		$replace = "https://".$a->get_hostname();
	} else {
		$search = "https://".$a->get_hostname();
		$replace = "http://".$a->get_hostname();
	}
	$path = str_replace($search, $replace, $path);
	return($path);
}
