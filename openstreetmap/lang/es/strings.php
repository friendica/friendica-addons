<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['View Larger'] = 'Ver mas grande';
$a->strings['Submit'] = 'Enviar';
$a->strings['Tile Server URL'] = 'URL del mosaico de servidor';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">public tile servers</a>'] = 'Una lista de <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">servidores de archivos públicos</a>';
$a->strings['Nominatim (reverse geocoding) Server URL'] = 'URL del servidor Nominatim (codificación geográfica inversa)';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servers</a>'] = 'Una lista de <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">servidores Nominatim</a>';
$a->strings['Default zoom'] = 'Zoom por defecto';
$a->strings['The default zoom level. (1:world, 18:highest, also depends on tile server)'] = 'El nivel de zoom predeterminado. (1: mundo, 18: más alto, también depende del servidor de mosaicos)';
$a->strings['Include marker on map'] = 'Incluir marcador en el mapa';
$a->strings['Include a marker on the map.'] = 'Incluir un marcador en el mapa.';
