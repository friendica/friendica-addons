<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Map"] = "Hartă";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Tile Server URL"] = "URL Server pentru Stratificare Hărți";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "O lista cu <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">servere publice cu tipuri de hărți</a>";
$a->strings["Default zoom"] = "Magnificare implicită";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "Nivelul implicit de magnificare. (1:nivel global, 18:cea mai mare)";
$a->strings["Settings updated."] = "Configurări actualizate.";
