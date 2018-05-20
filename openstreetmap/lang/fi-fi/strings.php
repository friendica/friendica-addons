<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Submit"] = "Lähetä";
$a->strings["Tile Server URL"] = "";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "";
$a->strings["Default zoom"] = "Oletuszoomaus";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "Oletuszoomaustaso (1: kaukaisin, 18: läheisin)";
$a->strings["Settings updated."] = "Asetukset päivitetty";
