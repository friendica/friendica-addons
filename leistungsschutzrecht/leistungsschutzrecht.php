<?php
/**
 * Name: Leistungsschutzrecht
 * Description: Only useful in germany: Remove data from snippets from members of the VG Media
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function leistungsschutzrecht_install() {
	register_hook('cron', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_cron');
	register_hook('getsiteinfo', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
	register_hook('page_info_data', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
}


function leistungsschutzrecht_uninstall() {
	unregister_hook('cron', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_cron');
	unregister_hook('getsiteinfo', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
	unregister_hook('page_info_data', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
}

function leistungsschutzrecht_getsiteinfo($a, &$siteinfo) {
	if (!isset($siteinfo["url"]))
		return;

	if (!leistungsschutzrecht_is_member_site($siteinfo["url"]))
		return;

	//$siteinfo["title"] = $siteinfo["url"];
	$siteinfo["text"] = leistungsschutzrecht_cuttext($siteinfo["text"]);
	unset($siteinfo["image"]);
	unset($siteinfo["images"]);
	unset($siteinfo["keywords"]);
}

function leistungsschutzrecht_cuttext($text) {
	$text = str_replace(array("\r", "\n"), array(" ", " "), $text);

	do {
		$oldtext = $text;
		$text = str_replace("  ", " ", $text);
	} while ($oldtext != $text);

	$words = explode(" ", $text);

	$text = "";
	$count = 0;
	$limit = 7;

	foreach ($words as $word) {
		if ($text != "")
			$text .= " ";

		$text .= $word;

		if (++$count >= $limit) {
			if (sizeof($words) > $limit)
				$text .= " ...";

			break;
		}
	}
	return $text;
}

function leistungsschutzrecht_fetchsites() {
	require_once("include/network.php");

	// This list works - but question is how current it is
	$url = "http://leistungsschutzrecht-stoppen.d-64.org/blacklist.txt";
	$sitelist = fetch_url($url);
	$siteurls = explode(',', $sitelist);

	$whitelist = array('tagesschau.de', 'heute.de', 'wdr.de');

	$sites = array();
	foreach ($siteurls AS $site) {
		if (!in_array($site, $whitelist)) {
			$sites[$site] = $site;
		}
	}

	// I would prefer parsing the list from the original site, but I haven't found a list.
	// The following stays here to possibly reenable it in the future without having to reinvent the wheel completely.
/*
	$sites = array();

	$url = "http://www.vg-media.de/lizenzen/digitale-verlegerische-angebote/wahrnehmungsberechtigte-digitale-verlegerische-angebote.html";

	$site = fetch_url($url);

	$doc = new DOMDocument();
	@$doc->loadHTML($site);

	$xpath = new DomXPath($doc);
	$list = $xpath->query("//td/a");
	foreach ($list as $node) {
		$attr = array();
		if ($node->attributes->length)
			foreach ($node->attributes as $attribute)
				$attr[$attribute->name] = $attribute->value;

		if (isset($attr["href"])) {
			$urldata = parse_url($attr["href"]);

			if (isset($urldata["host"]) && !isset($urldata["path"])) {
				$cleanedurlpart = explode("%", $urldata["host"]);

				$hostname = explode(".", $cleanedurlpart[0]);
				$site = $hostname[sizeof($hostname) - 2].".".$hostname[sizeof($hostname) - 1];
				$sites[$site] = $site;
			}
		}
	}
*/

	if (sizeof($sites)) {
		set_config('leistungsschutzrecht','sites',$sites);
	}
}

function leistungsschutzrecht_is_member_site($url) {
	$sites = get_config('leistungsschutzrecht','sites');

	if ($sites == "")
		return(false);

	if (sizeof($sites) == 0)
		return(false);

	$urldata = parse_url($url);

	if (!isset($urldata["host"]))
		return(false);

	$cleanedurlpart = explode("%", $urldata["host"]);

	$hostname = explode(".", $cleanedurlpart[0]);
	$site = $hostname[sizeof($hostname) - 2].".".$hostname[sizeof($hostname) - 1];

	return (isset($sites[$site]));
}

function leistungsschutzrecht_cron($a,$b) {
	$last = get_config('leistungsschutzrecht','last_poll');

	if($last) {
		$next = $last + 86400;
		if($next > time()) {
			logger('poll intervall not reached');
			return;
		}
	}
	leistungsschutzrecht_fetchsites();
	set_config('leistungsschutzrecht','last_poll', time());
}
?>
