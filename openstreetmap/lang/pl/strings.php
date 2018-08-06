<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Submit"] = "Zatwierdź";
$a->strings["Tile Server URL"] = "Adres URL serwera sąsiadująco";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "Lista <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">serwerów publicznych</a>";
$a->strings["Default zoom"] = "Domyślne powiększenie";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "Domyślny poziom powiększenia. (1:świat, 18:najwyższy)";
$a->strings["Settings updated."] = "Zaktualizowano ustawienia.";
