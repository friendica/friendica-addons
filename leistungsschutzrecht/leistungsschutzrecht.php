<?php
/**
 * Name: Leistungsschutzrecht
 * Description: Only useful in germany: Remove data from snippets from members of the VG Media
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\DI;

function leistungsschutzrecht_install() {
	Hook::register('cron', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_cron');
	Hook::register('getsiteinfo', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
	Hook::register('page_info_data', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
}


function leistungsschutzrecht_uninstall() {
	Hook::unregister('cron', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_cron');
	Hook::unregister('getsiteinfo', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
	Hook::unregister('page_info_data', 'addon/leistungsschutzrecht/leistungsschutzrecht.php', 'leistungsschutzrecht_getsiteinfo');
}

function leistungsschutzrecht_getsiteinfo($a, &$siteinfo) {
	if (!isset($siteinfo["url"]) || empty($siteinfo['type'])) {
		return;
	}

	// Avoid any third party pictures, to avoid copyright issues
	if (!in_array($siteinfo['type'], ['photo', 'video']) && DI::config()->get('leistungsschutzrecht', 'suppress_photos', false)) {
		unset($siteinfo["image"]);
		unset($siteinfo["images"]);
	}

	if (!leistungsschutzrecht_is_member_site($siteinfo["url"])) {
		return;
	}

	if (!empty($siteinfo["text"])) {
		$siteinfo["text"] = leistungsschutzrecht_cuttext($siteinfo["text"]);
	}

	unset($siteinfo["keywords"]);
}

function leistungsschutzrecht_cuttext($text) {
	$text = str_replace(["\r", "\n"], [" ", " "], $text);

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

function leistungsschutzrecht_fetchsites()
{
	// This list works - but question is how current it is
	$url = "http://leistungsschutzrecht-stoppen.d-64.org/blacklist.txt";
	$sitelist = DI::httpRequest()->fetch($url);
	$siteurls = explode(',', $sitelist);

	$whitelist = ['tagesschau.de', 'heute.de', 'wdr.de'];

	$sites = [];
	foreach ($siteurls as $site) {
		if (!in_array($site, $whitelist)) {
			$sites[$site] = $site;
		}
	}

	// I would prefer parsing the list from the original site, but I haven't found a list.
	// The following stays here to possibly reenable it in the future without having to reinvent the wheel completely.
/*
	$sites = array();

	$url = "http://www.vg-media.de/lizenzen/digitale-verlegerische-angebote/wahrnehmungsberechtigte-digitale-verlegerische-angebote.html";

	$site = Network::fetchUrl($url);

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
		DI::config()->set('leistungsschutzrecht','sites',$sites);
	}
}

function leistungsschutzrecht_is_member_site($url) {
	$sites = DI::config()->get('leistungsschutzrecht','sites');

	if ($sites == "")
		return(false);

	if (sizeof($sites) == 0)
		return(false);

	$urldata = parse_url($url);

	if (!isset($urldata["host"]))
		return(false);

	$cleanedurlpart = explode("%", $urldata["host"]);

	$hostname = explode(".", $cleanedurlpart[0]);
	if (empty($hostname)) {
		return false;
	}

	if (count($hostname) <= 2) {
		return false;
	}

	$site = $hostname[sizeof($hostname) - 2].".".$hostname[sizeof($hostname) - 1];

	return (isset($sites[$site]));
}

function leistungsschutzrecht_cron($a,$b) {
	$last = DI::config()->get('leistungsschutzrecht','last_poll');

	if($last) {
		$next = $last + 86400;
		if($next > time()) {
			Logger::log('poll intervall not reached');
			return;
		}
	}
	leistungsschutzrecht_fetchsites();
	DI::config()->set('leistungsschutzrecht','last_poll', time());
}
?>
