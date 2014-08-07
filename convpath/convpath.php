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
	register_hook('ping_xmlize',  'addon/convpath/convpath.php', 'convpath_ping_xmlize_hook');
	register_hook('prepare_body', 'addon/convpath/convpath.php', 'convpath_prepare_body_hook');
	register_hook('display_item', 'addon/convpath/convpath.php', 'convpath_display_item_hook');
}


function convpath_uninstall() {
	unregister_hook('page_end', 'addon/convpath/convpath.php', 'convpath_page_end');
	unregister_hook('page_header', 'addon/convpath/convpath.php', 'convpath_page_header');
	unregister_hook('ping_xmlize',  'addon/convpath/convpath.php', 'convpath_ping_xmlize_hook');
	unregister_hook('prepare_body', 'addon/convpath/convpath.php', 'convpath_prepare_body_hook');
	unregister_hook('display_item', 'addon/convpath/convpath.php', 'convpath_display_item_hook');
}

function convpath_ping_xmlize_hook(&$a, &$o) {
	$o["photo"] = convpath_url($a, $o["photo"]);
}

function convpath_page_header(&$a, &$o){
	$o = convpath_convert($o);
}

function convpath_page_end(&$a, &$o){
	$o = convpath_convert($o);
	if (isset($a->page['aside']))
		$a->page['aside'] = convpath_convert($a->page['aside']);
}

function convpath_prepare_body_hook(&$a, &$o) {
	$o["html"] = convpath_convert($o["html"]);
}

function convpath_display_item_hook(&$a, &$o) {
	if (isset($o["output"])) {
		if (isset($o["output"]["thumb"]))
			$o["output"]["thumb"] = convpath_url($a, $o["output"]["thumb"]);
		if (isset($o["output"]["author-avatar"]))
			$o["output"]["author-avatar"] = convpath_url($a, $o["output"]["author-avatar"]);
		if (isset($o["output"]["owner-avatar"]))
			$o["output"]["owner-avatar"] = convpath_url($a, $o["output"]["owner-avatar"]);
		if (isset($o["output"]["owner_photo"]))
			$o["output"]["owner_photo"] = convpath_url($a, $o["output"]["owner_photo"]);
	}
}

function convpath_url($a, $path) {
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
	$searcharr = array("src='".$search, 'src="'.$search);
	$replacearr = array("src='".$replace, 'src="'.$replace);
	$path = str_replace($searcharr, $replacearr, $path);

	//$path = str_replace($search, $replace, $path);

	return($path);
}
