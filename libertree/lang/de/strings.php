<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to libertree"] = "bei libertree veröffentlichen";
$a->strings["libertree Export"] = "libertree Export";
$a->strings["Enable Libertree Post Addon"] = "Libertree-Post-Addon aktivieren";
$a->strings["Libertree API token"] = "Libertree-API-Token";
$a->strings["Libertree site URL"] = "Libertree-URL";
$a->strings["Post to Libertree by default"] = "Standardmäßig bei libertree veröffentlichen";
$a->strings["Save Settings"] = "Einstellungen speichern";
