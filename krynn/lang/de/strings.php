<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Krynn Settings"] = "Planeten Einstellungen";
$a->strings["Enable Krynn Addon"] = "Planeten-Addon aktivieren";
$a->strings["Submit"] = "Senden";
