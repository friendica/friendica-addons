<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Geonames Settings"] = "Geonames-Einstellungen";
$a->strings["Replace numerical coordinates by the nearest populated location name in your posts."] = "Ersetze numerische Koordinaten in Beiträgen mit dem Namen der nächst gelegenen Siedlung.";
$a->strings["Enable Geonames Addon"] = "Geonames-Addon aktivieren";
$a->strings["Save Settings"] = "Einstellungen speichern";
