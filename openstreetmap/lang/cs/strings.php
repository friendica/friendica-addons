<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n==1)) { return 0; } else if (($n>=2 && $n<=4)) { return 1; } else  { return 2; }
}}
;
$a->strings["Submit"] = "Odeslat";
$a->strings["Tile Server URL"] = "URL adresa Tile serveru";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "Seznam <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">veřejných tile serverů</a>";
$a->strings["Default zoom"] = "Defaultní lupa";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "Defaultní nastavení lupy (1:svět, 18:nejvyšší)";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
