<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Randplace Settings"] = "Randplace-Einstellungen";
$a->strings["Enable Randplace Addon"] = "Randplace-Addon aktivieren";
$a->strings["Save Settings"] = "Einstellungen speichern";
