<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['View Larger'] = 'Vedi Ingrandito';
$a->strings['Submit'] = 'Invia';
$a->strings['Tile Server URL'] = 'Indirizzo del server dei tasselli';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">public tile servers</a>'] = 'Una lista dei <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">server dei tasselli pubblici</a>';
$a->strings['Nominatim (reverse geocoding) Server URL'] = 'URL Server Nominatim (reverse geocoding)';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servers</a>'] = 'Una lista dei <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">server Nominatim</a>';
$a->strings['Default zoom'] = 'Zoom predefinito';
$a->strings['The default zoom level. (1:world, 18:highest, also depends on tile server)'] = 'Il livello di zoom predefinito. (1:mondo, 18:massimo, ma dipende dal server di tasselli)';
$a->strings['Include marker on map'] = 'Includi segnaposto sulla mappa';
$a->strings['Include a marker on the map.'] = 'Includi un segnaposto sulla mappa.';
