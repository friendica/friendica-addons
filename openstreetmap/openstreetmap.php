<?php
/**
 * Name: Open Street Map
 * Description: Use openstreetmap.org for displaying locations.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
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

	$location = '';
	$coord = '';

	$location = (($item['location']) ? '<a target="map" title="' . $item['location'] . '" href="http://www.openstreetmap.org/?q=' . urlencode($item['location']) . '">' . $item['location'] . '</a>' : '');

	if($item['coord']) {
		$coords = explode(' ', $item['coord']);
		if(count($coords) > 1) {
			$coord = '<a target="map" title="' . $item['coord'] . '" href="http://www.openstreetmap.org/?lat=' . urlencode($coords[0]) . '&lon=' . urlencode($coords[1]) . '&zoom=10">' . $item['coord'] . '</a>' ;
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

