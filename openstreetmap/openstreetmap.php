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
 
require_once('include/cache.php');


function openstreetmap_install() {
	register_hook('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');
	register_hook('generate_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_map');
	register_hook('generate_named_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_named_map');
	register_hook('page_header', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_alterheader');

	logger("installed openstreetmap");
}

function openstreetmap_uninstall() {
	unregister_hook('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');
	unregister_hook('generate_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_map');
	unregister_hook('generate_named_map', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_generate_named_map');
	unregister_hook('page_header', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_alterheader');

	logger("removed openstreetmap");
}

function openstreetmap_alterheader($a, &$navHtml) {
	$addScriptTag = '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/openstreetmap/openstreetmap.js"></script>' . "\r\n";
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
function openstreetmap_location($a, &$item) {

	if(! (strlen($item['location']) || strlen($item['coord'])))
		return;

	/*
	 * Get the configuration variables from the config.
	 * @todo Separate the tile map server from the text-string to map tile server
	 * since they apparently use different URL conventions.
	 * We use OSM's current convention of "#map=zoom/lat/lon" and optional
	 * ?mlat=lat&mlon=lon for markers.
	 */

	$tmsserver = get_config('openstreetmap', 'tmsserver');
	if(! $tmsserver)
		$tmsserver = 'http://www.openstreetmap.org';

	$nomserver = get_config('openstreetmap', 'nomserver');
	if(! $nomserver)
		$nomserver = 'http://nominatim.openstreetmap.org/search.php';

	$zoom = get_config('openstreetmap', 'zoom');
	if(! $zoom)
		$zoom = 16;

	$marker = get_config('openstreetmap', 'marker');
	if(! $marker)
		$marker = 0;

	if ($item['coord'] != "") {
		$coords = explode(' ', $item['coord']);
		if(count($coords) > 1) {
			$lat = urlencode(round($coords[0], 5));
			$lon = urlencode(round($coords[1], 5));
			$target = $tmsserver;
			if($marker > 0)
				$target .= '?mlat='.$lat.'&mlon='.$lon;
			$target .= '#map='.intval($zoom).'/'.$lat.'/'.$lon;
		}
	}

	if ($target == "")
		$target = $nomserver.'?q='.urlencode($item['location']);

	if ($item['location'] != "")
		$title = $item['location'];
	else
		$title = $item['coord'];

	$item['html'] = '<a target="map" title="'.$title.'" href= "'.$target.'">'.$title.'</a>';
}


function openstreetmap_generate_named_map(&$a,&$b) {


	$nomserver = get_config('openstreetmap', 'nomserver');
	if(! $nomserver)
		$nomserver = 'http://nominatim.openstreetmap.org/search.php';
	$args = '?q=' . urlencode($b['location']) . '&format=json';

	$x = z_fetch_url($nomserver . $args);
	if($x['success']) {
		$j = json_decode($x['body'],true);

		if($j && is_array($j) && $j[0]['lat'] && $j[0]['lon']) {
			$arr = array('lat' => $j[0]['lat'],'lon' => $j[0]['lon'],'location' => $b['location'], 'html' => '');
			openstreetmap_generate_map($a,$arr);
			$b['html'] = $arr['html'];
		}
	}
}

function openstreetmap_generate_map(&$a,&$b) {

	$tmsserver = get_config('openstreetmap', 'tmsserver');
	if(! $tmsserver)
		$tmsserver = 'http://www.openstreetmap.org';
	if(strpos(z_root(),'https:') !== false)
		$tmsserver = str_replace('http:','https:',$tmsserver);


	$zoom = get_config('openstreetmap', 'zoom');
	if(! $zoom)
		$zoom = 16;

	$marker = get_config('openstreetmap', 'marker');
	if(! $marker)
		$marker = 0;

	$lat = $b['lat']; // round($b['lat'], 5);
	$lon = $b['lon']; // round($b['lon'], 5);

	logger('lat: ' . $lat, LOGGER_DATA);
	logger('lon: ' . $lon, LOGGER_DATA);


	$b['html'] = '<iframe style="width:100%; height:300px; border:1px solid #ccc" src="' . $tmsserver . '/export/embed.html?bbox=' . ($lon - 0.01) . '%2C' . ($lat - 0.01) . '%2C' . ($lon + 0.01) . '%2C' . ($lat + 0.01) ;

	$b['html'] .=  '&amp;layer=mapnik&amp;marker=' . $lat . '%2C' . $lon . '" style="border: 1px solid black"></iframe><br/><small><a href="' . $tmsserver . '/?mlat=' . $lat . '&mlon=' . $lon . '#map=16/' . $lat . '/' . $lon . '">' . (($b['location']) ? escape_tags($b['location']) : t('View Larger')) . '</a></small>';

	logger('generate_map: ' . $b['html'], LOGGER_DATA);

}

function openstreetmap_plugin_admin(&$a, &$o) {
	$t = get_markup_template("admin.tpl", "addon/openstreetmap/");
	$tmsserver = get_config('openstreetmap', 'tmsserver');
	if(! $tmsserver)
		$tmsserver = 'http://www.openstreetmap.org';
	$nomserver = get_config('openstreetmap', 'nomserver');
	if(! $nomserver)
		$nomserver = 'http://nominatim.openstreetmap.org/search.php';
	$zoom = get_config('openstreetmap', 'zoom');
	if(! $zoom)
		$zoom = 16;
	$marker = get_config('openstreetmap', 'marker');
	if(! $marker)
		$marker = 0;

	$o = replace_macros($t, array(
			'$submit' => t('Submit'),
			'$tmsserver' => array('tmsserver', t('Tile Server URL'), $tmsserver, t('A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">public tile servers</a>')),
			'$nomserver' => array('nomserver', t('Nominatim (reverse geocoding) Server URL'), $nomserver, t('A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank">Nominatim servers</a>')),
			'$zoom' => array('zoom', t('Default zoom'), $zoom, t('The default zoom level. (1:world, 18:highest, also depends on tile server)')),
			'$marker' => array('marker', t('Include marker on map'), $marker, t('Include a marker on the map.')),
	));
}
function openstreetmap_plugin_admin_post(&$a) {
	$urltms = ((x($_POST, 'tmsserver')) ? notags(trim($_POST['tmsserver'])) : '');
	$urlnom = ((x($_POST, 'nomserver')) ? notags(trim($_POST['nomserver'])) : '');
	$zoom = ((x($_POST, 'zoom')) ? intval(trim($_POST['zoom'])) : '16');
	$marker = ((x($_POST, 'marker')) ? intval(trim($_POST['marker'])) : '0');
	set_config('openstreetmap', 'tmsserver', $urltms);
	set_config('openstreetmap', 'nomserver', $urlnom);
	set_config('openstreetmap', 'zoom', $zoom);
	set_config('openstreetmap', 'marker', $marker);
	info( t('Settings updated.') . EOL);
}


