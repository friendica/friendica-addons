<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return intval($n % 10 != 1 || $n % 100 == 11);
}}
;
$a->strings["Submit"] = "Senda inn";
$a->strings["Tile Server URL"] = "Slóð á kortaflísamiðlara";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "Listi yfir <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">opinbera kortaflísamiðlara</a>";
$a->strings["Default zoom"] = "Sjálfgefinn aðdráttur";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "Sjálfgefið aðdráttarstig. (1:heimur, 18:mest)";
$a->strings["Settings updated."] = "Stillingar uppfærðar.";
