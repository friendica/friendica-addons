<?php
/**
 * Name: OpenStreetMap
 * Description: Use OpenStreetMap for displaying locations.  After activation the post location just beneath your avatar in your posts will link to openstreetmap.
 * Version: 1.1
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Klaus Weidenbach
 *
 */

function openstreetmap_install() {
	register_hook('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');

	logger("installed openstreetmap");
}

function openstreetmap_uninstall() {
	unregister_hook('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');

	logger("removed openstreetmap");
}


function openstreetmap_location($a, &$item) {
	if(! (strlen($item['location']) || strlen($item['coord'])))
		return; 

	/*
	 * Get the configuration variables from the .htconfig file.
	 */
	$tmsserver = get_config('openstreetmap','tmsserver');
	if(! $tmsserver)
		$tmsserver = 'http://openstreetmap.org';
	$zoom = get_config('openstreetmap','zoom');
	if(! $zoom)
		$zoom = 17;

	$location = '';
	$coord = '';

	$location = (($item['location']) ? '<a target="map" title="' . $item['location'] . '" href="'.$tmsserver.'?q=' . urlencode($item['location']) . '">' . $item['location'] . '</a>' : '');

	if($item['coord']) {
		$coords = explode(' ', $item['coord']);
		if(count($coords) > 1) {
			$coord = '<a target="map" title="' . $item['coord'] . '" href="'.$tmsserver.'?lat=' . urlencode($coords[0]) . '&lon=' . urlencode($coords[1]) . '&zoom='.$zoom.'">' . $item['coord'] . '</a>' ;
		}
	}
	if(strlen($coord)) {
		if($location)
			$location .= '<br /><span class="smalltext">(' . $coord . ')</span>';
		else
			$location = '<span class="smalltext">' . $coord . '</span>';
	}
	$item['html'] = $location;
	return;
}


function openstreetmap_plugin_admin (&$a, &$o) {
	$t = file_get_contents( dirname(__file__)."/admin.tpl");
	$tmsserver = get_config('openstreetmap','tmsserver');
	if(! $tmsserver)
		$tmsserver = 'http://openstreetmap.org';
	$zoom = get_config('openstreetmap','zoom');
	if(! $zoom)
		$zoom = 17;

	$o = replace_macros( $t, array(
		'$submit' => t('Submit'),
		'$tmsserver' => array('tmsserver', t('Tile Server URL'), $tmsserver, t('A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">public tile servers</a>')),
		'$zoom' => array('zoom', t('Default zoom'), $zoom, t('The default zoom level. (1:world, 18:highest)')),
	));
}
function openstreetmap_plugin_admin_post (&$a) {
	$url = ((x($_POST, 'tmsserver')) ? notags(trim($_POST['tmsserver'])) : '');
	$zoom = ((x($_POST, 'zoom')) ? intval(trim($_POST['zoom'])) : '17');
	set_config('openstreetmap', 'tmsserver', $url);
	set_config('openstreetmap', 'zoom', $zoom);
	info( t('Settings updated.'). EOL);
}
