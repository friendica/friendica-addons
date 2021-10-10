<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['View Larger'] = 'Nagyobb megtekintése';
$a->strings['Submit'] = 'Elküldés';
$a->strings['Tile Server URL'] = 'Csempekiszolgáló URL';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">public tile servers</a>'] = '<a href="https://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">Nyilvános csempekiszolgálók</a> listája';
$a->strings['Nominatim (reverse geocoding) Server URL'] = 'Nominatim (fordított geokódolás) kiszolgáló URL';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servers</a>'] = '<a href="https://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim kiszolgálók</a> listája';
$a->strings['Default zoom'] = 'Alapértelmezett nagyítás';
$a->strings['The default zoom level. (1:world, 18:highest, also depends on tile server)'] = 'Az alapértelmezett nagyítási szint (1: világ, 18: legnagyobb, de függ a csempekiszolgálótól is).';
$a->strings['Include marker on map'] = 'Jelölő felvétele a térképre';
$a->strings['Include a marker on the map.'] = 'Egy jelölő felvétele a térképre.';
