<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Map"] = "Mapa";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Tile Server URL"] = "URL adresa Tile serveru";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "Seznam <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">veřejných tile serverů</a>";
$a->strings["Default zoom"] = "Defaultní lupa";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "Defaultní nastavení lupy (1:svět, 18:nejvyšší)";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
