<?php
/**
 * Name: OpenStreetMap
 * Description: Use OpenStreetMap for displaying locations. After activation the post location just beneath your avatar in your posts will link to OpenStreetMap.
 * Version: 1.3.1
 * Author: Fabio <http://kirgroup.com/~fabrixxm>
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Klaus Weidenbach
 *
 */

use Friendica\Core\Cache;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Util\ConfigFileLoader;
use Friendica\Util\Network;
use Friendica\Util\Strings;

const OSM_TMS = 'https://www.openstreetmap.org';
const OSM_NOM = 'https://nominatim.openstreetmap.org/search.php';
const OSM_ZOOM = 16;
const OSM_MARKER = 0;

function openstreetmap_install()
{
	Hook::register('load_config',     'addon/openstreetmap/openstreetmap.php', 'openstreetmap_load_config');
	Hook::register('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');
	Hook::register('generate_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_map');
	Hook::register('generate_named_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_named_map');
	Hook::register('Map::getCoordinates', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_get_coordinates');
	Hook::register('page_header', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_alterheader');

	Logger::log("installed openstreetmap");
}

function openstreetmap_uninstall()
{
	Hook::unregister('load_config',     'addon/openstreetmap/openstreetmap.php', 'openstreetmap_load_config');
	Hook::unregister('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');
	Hook::unregister('generate_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_map');
	Hook::unregister('generate_named_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_named_map');
	Hook::unregister('Map::getCoordinates', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_get_coordinates');
	Hook::unregister('page_header', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_alterheader');

	Logger::log("removed openstreetmap");
}

function openstreetmap_load_config(\Friendica\App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('openstreetmap'));
}

function openstreetmap_alterheader($a, &$navHtml)
{
	$addScriptTag = '<script type="text/javascript" src="' . $a->getBaseURL() . '/addon/openstreetmap/openstreetmap.js"></script>' . "\r\n";
	$a->page['htmlhead'] .= $addScriptTag;
}

/**
 * @brief Add link to a map for an item's set location/coordinates.
 *
 * If an item has coordinates add link to a tile map server, e.g. openstreetmap.org.
 * If an item has a location open it with the help of OSM's Nominatim reverse geocode search.
 *
 * @param mixed $a
 * @param array& $item
 */
function openstreetmap_location($a, &$item)
{
	if (!(strlen($item['location']) || strlen($item['coord']))) {
		return;
	}

	/*
	 * Get the configuration variables from the config.
	 * @todo Separate the tile map server from the text-string to map tile server
	 * since they apparently use different URL conventions.
	 * We use OSM's current convention of "#map=zoom/lat/lon" and optional
	 * ?mlat=lat&mlon=lon for markers.
	 */

	$tmsserver = Config::get('openstreetmap', 'tmsserver', OSM_TMS);
	$nomserver = Config::get('openstreetmap', 'nomserver', OSM_NOM);
	$zoom = Config::get('openstreetmap', 'zoom', OSM_ZOOM);
	$marker = Config::get('openstreetmap', 'marker', OSM_MARKER);

	// This is needed since we stored an empty string in the config in previous versions
	if (empty($nomserver)) {
		$nomserver = OSM_NOM;
	}

	if ($item['coord'] != "") {
		$coords = explode(' ', $item['coord']);
		if (count($coords) > 1) {
			$lat = urlencode(round($coords[0], 5));
			$lon = urlencode(round($coords[1], 5));
			$target = $tmsserver;
			if ($marker > 0) {
				$target .= '?mlat=' . $lat . '&mlon=' . $lon;
			}
			$target .= '#map='.intval($zoom).'/'.$lat.'/'.$lon;
		}
	}

	if (empty($target)) {
		$target = $nomserver.'?q='.urlencode($item['location']);
	}

	if ($item['location'] != "") {
		$title = $item['location'];
	} else {
		$title = $item['coord'];
	}

	$item['html'] = '<a target="map" title="'.$title.'" href= "'.$target.'">'.$title.'</a>';
}

function openstreetmap_get_coordinates($a, &$b)
{
	$nomserver = Config::get('openstreetmap', 'nomserver', OSM_NOM);

	// This is needed since we stored an empty string in the config in previous versions
	if (empty($nomserver)) {
		$nomserver = OSM_NOM;
	}

	$args = '?q=' . urlencode($b['location']) . '&format=json';

	$cachekey = "openstreetmap:" . $b['location'];
	$j = Cache::get($cachekey);

	if (is_null($j)) {
		$curlResult = Network::curl($nomserver . $args);
		if ($curlResult->isSuccess()) {
			$j = json_decode($curlResult->getBody(), true);
			Cache::set($cachekey, $j, Cache::MONTH);
		}
	}

	if (!empty($j[0]['lat']) && !empty($j[0]['lon'])) {
		$b['lat'] = $j[0]['lat'];
		$b['lon'] = $j[0]['lon'];
	}
}

function openstreetmap_generate_named_map(&$a, &$b)
{
	openstreetmap_get_coordinates($a, $b);

	if (!empty($b['lat']) && !empty($b['lon'])) {
		openstreetmap_generate_map($a, $b);
	}
}

function openstreetmap_generate_map(&$a, &$b)
{
	$tmsserver = Config::get('openstreetmap', 'tmsserver', OSM_TMS);

	if (strpos($a->getBaseURL(true), 'https:') !== false) {
		$tmsserver = str_replace('http:','https:',$tmsserver);
	}

	$zoom = Config::get('openstreetmap', 'zoom', OSM_ZOOM);
	$marker = Config::get('openstreetmap', 'marker', OSM_MARKER);

	$lat = $b['lat']; // round($b['lat'], 5);
	$lon = $b['lon']; // round($b['lon'], 5);

	Logger::log('lat: ' . $lat, Logger::DATA);
	Logger::log('lon: ' . $lon, Logger::DATA);

	$cardlink = '<a href="' . $tmsserver;

	if ($marker > 0) {
		$cardlink .= '?mlat=' . $lat . '&mlon=' . $lon;
	}

	$cardlink .= '#map=' . $zoom . '/' . $lat . '/' . $lon . '">' . ($b['location'] ? Strings::escapeHtml($b['location']) : L10n::t('View Larger')) . '</a>';
	if (empty($b['mode'])) {
		$b['html'] = '<iframe style="width:100%; height:300px; border:1px solid #ccc" src="' . $tmsserver .
				'/export/embed.html?bbox=' . ($lon - 0.01) . '%2C' . ($lat - 0.01) . '%2C' . ($lon + 0.01) . '%2C' . ($lat + 0.01) .
				'&amp;layer=mapnik&amp;marker=' . $lat . '%2C' . $lon . '" style="border: 1px solid black"></iframe>' .
				'<br/><small>' . $cardlink . '</small>';
	} else {
		$b['html'] .= '<br/>' . $cardlink;
	}

	Logger::log('generate_map: ' . $b['html'], Logger::DATA);
}

function openstreetmap_addon_admin(&$a, &$o)
{
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/openstreetmap/");
	$tmsserver = Config::get('openstreetmap', 'tmsserver', OSM_TMS);
	$nomserver = Config::get('openstreetmap', 'nomserver', OSM_NOM);
	$zoom = Config::get('openstreetmap', 'zoom', OSM_ZOOM);
	$marker = Config::get('openstreetmap', 'marker', OSM_MARKER);

	// This is needed since we stored an empty string in the config in previous versions
	if (empty($nomserver)) {
		$nomserver = OSM_NOM;
	}

	$o = Renderer::replaceMacros($t, [
			'$submit' => L10n::t('Submit'),
			'$tmsserver' => ['tmsserver', L10n::t('Tile Server URL'), $tmsserver, L10n::t('A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">public tile servers</a>')],
			'$nomserver' => ['nomserver', L10n::t('Nominatim (reverse geocoding) Server URL'), $nomserver, L10n::t('A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank">Nominatim servers</a>')],
			'$zoom' => ['zoom', L10n::t('Default zoom'), $zoom, L10n::t('The default zoom level. (1:world, 18:highest, also depends on tile server)')],
			'$marker' => ['marker', L10n::t('Include marker on map'), $marker, L10n::t('Include a marker on the map.')],
	]);
}

function openstreetmap_addon_admin_post(&$a)
{
	$urltms = ($_POST['tmsserver'] ?? '') ?: OSM_TMS;
	$urlnom = ($_POST['nomserver'] ?? '') ?: OSM_NOM;
	$zoom = ($_POST['zoom'] ?? '') ?: OSM_ZOOM;
	$marker = ($_POST['marker'] ?? '') ?: OSM_MARKER;

	Config::set('openstreetmap', 'tmsserver', $urltms);
	Config::set('openstreetmap', 'nomserver', $urlnom);
	Config::set('openstreetmap', 'zoom', $zoom);
	Config::set('openstreetmap', 'marker', $marker);

	info(L10n::t('Settings updated.') . EOL);
}
