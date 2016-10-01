<?php
/**
 * Name: Google Maps
 * Description: Use Google Maps for displaying locations. After activation the post location just beneath your avatar in your posts will link to Google Maps.
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>.
 */
require_once 'include/cache.php';

function googlemaps_install()
{
    register_hook('render_location', 'addon/googlemaps/googlemaps.php', 'googlemaps_location');

    logger('installed googlemaps');
}

function googlemaps_uninstall()
{
    unregister_hook('render_location', 'addon/googlemaps/googlemaps.php', 'googlemaps_location');

    logger('removed googlemaps');
}

function googlemaps_location($a, &$item)
{
    if (!(strlen($item['location']) || strlen($item['coord']))) {
        return;
    }

    if ($item['coord'] != '') {
        $target = 'http://maps.google.com/?q='.urlencode($item['coord']);
    } else {
        $target = 'http://maps.google.com/?q='.urlencode($item['location']);
    }

    if ($item['location'] != '') {
        $title = $item['location'];
    } else {
        $title = $item['coord'];
    }

    $item['html'] = '<a target="map" title="'.$title.'" href= "'.$target.'">'.$title.'</a>';
}
