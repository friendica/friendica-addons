<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Submit"] = "Opslaan";
$a->strings["Tile Server URL"] = "URL met kaarttegels";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "";
$a->strings["Default zoom"] = "";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "";
$a->strings["Settings updated."] = "Instellingen opgeslagen";
