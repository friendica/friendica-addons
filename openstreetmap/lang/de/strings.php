<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['View Larger'] = 'Vergrößern';
$a->strings['Submit'] = 'Senden';
$a->strings['Tile Server URL'] = 'Die URL des Servers';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">public tile servers</a>'] = 'Eine Liste <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">öffentlicher Tile-Server</a>';
$a->strings['Nominatim (reverse geocoding) Server URL'] = 'Nominatim (umgekehrte Geokodierung) Server URL';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servers</a>'] = 'Eine Liste von <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim Servern</a>';
$a->strings['Default zoom'] = 'Standard-Zoom';
$a->strings['The default zoom level. (1:world, 18:highest, also depends on tile server)'] = 'Das voreingestellte Zoom Level (1 Welt, 18 höchstes; hängt auch vom Tile-Server ab)';
$a->strings['Include marker on map'] = 'Markierung auf der Karte anzeigen';
$a->strings['Include a marker on the map.'] = 'Eine Markierung auf der Karte anzeigen.';
