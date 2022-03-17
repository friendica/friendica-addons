<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['View Larger'] = 'Zobacz większą';
$a->strings['Submit'] = 'Zatwierdź';
$a->strings['Tile Server URL'] = 'Adres URL serwera kafelków';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">public tile servers</a>'] = 'Lista <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank" rel="noopener noreferrer">publicznych serwerów z kafelkami</a>';
$a->strings['Nominatim (reverse geocoding) Server URL'] = 'Adres URL serwera Nominatim (geokodowanie odwrotne)';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">Nominatim servers</a>'] = 'Lista <a href="http://wiki.openstreetmap.org/wiki/Nominatim" target="_blank" rel="noopener noreferrer">serwerów Nominatim</a>';
$a->strings['Default zoom'] = 'Domyślne powiększenie';
$a->strings['The default zoom level. (1:world, 18:highest, also depends on tile server)'] = 'Domyślny poziom powiększenia. (1:świat, 18:największy; zależy to także od serwera kafelków)';
$a->strings['Include marker on map'] = 'Uwzględnij znacznik na mapie';
$a->strings['Include a marker on the map.'] = 'Uwzględnij znacznik na mapie.';
