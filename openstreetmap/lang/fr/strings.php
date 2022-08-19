<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['View Larger'] = 'Voir en plus grand';
$a->strings['Submit'] = 'Envoyer';
$a->strings['Tile Server URL'] = 'URL du serveur de tuiles';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">public tile servers</a>'] = 'Liste de <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">serveurs de tuile publics</a>';
$a->strings['Nominatim (reverse geocoding) Server URL'] = 'URL de server Nominatim (geocoding inversé)';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servers</a>'] = 'Liste de <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">serveurs Nominatim</a>';
$a->strings['Default zoom'] = 'Zoom par défaut';
$a->strings['The default zoom level. (1:world, 18:highest, also depends on tile server)'] = 'Niveau de zoom par défaut (1:monde, 18:le plus proche, dépend également du serveur de tuile).';
$a->strings['Include marker on map'] = 'Inclut un marqueur sur la carte';
$a->strings['Include a marker on the map.'] = 'Inclut un marqueur sur la carte.';
