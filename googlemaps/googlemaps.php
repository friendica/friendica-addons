<?php
/**
 * Name: Google Maps
 * Description: Use Google Maps for displaying locations. After activation the post location just beneath your avatar in your posts will link to Google Maps.
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Cache;

function googlemaps_install()
{
	Addon::registerHook('render_location', 'addon/googlemaps/googlemaps.php', 'googlemaps_location');

	logger("installed googlemaps");
}

function googlemaps_uninstall()
{
	Addon::unregisterHook('render_location', 'addon/googlemaps/googlemaps.php', 'googlemaps_location');

	logger("removed googlemaps");
}

function googlemaps_location(App $a, array &$item)
{

	if (! (strlen($item['location']) || strlen($item['coord']))) {
		return;
	}

	if (x($item, 'coord')) {
		$target = "http://maps.google.com/?q=".urlencode($item['coord']);
	} else {
		$target = "http://maps.google.com/?q=".urlencode($item['location']);
	}

	if (x($item, 'location')) {
		$title = $item['location'];
	} else {
		$title = $item['coord'];
	}

	$item['html'] = '<a target="map" title="'.$title.'" href= "'.$target.'">'.$title.'</a>';
}
