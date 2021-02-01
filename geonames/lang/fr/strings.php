<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Geonames settings updated."] = "Paramètres de Geonames mis à jour.";
$a->strings["Geonames Settings"] = "Paramètres Geonames";
