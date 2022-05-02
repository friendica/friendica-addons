<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['View Larger'] = 'Vis større';
$a->strings['Submit'] = 'Indsend';
$a->strings['Tile Server URL'] = 'Fliseserver URL';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">public tile servers</a>'] = 'En liste af <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">offentlige fliseservere</a>';
$a->strings['Nominatim (reverse geocoding) Server URL'] = 'Nominatim (omvendt geokodning) Server URL';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servers</a>'] = 'En liste af <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servere</a>';
$a->strings['Default zoom'] = 'Standard zoom';
$a->strings['The default zoom level. (1:world, 18:highest, also depends on tile server)'] = 'Det normale zoom niveau. (1:verden, 18:højest, afhænger også af fliseserveren)';
$a->strings['Include marker on map'] = 'Inkluder markør på kort';
$a->strings['Include a marker on the map.'] = 'Inkluder en markør på kortet.';
