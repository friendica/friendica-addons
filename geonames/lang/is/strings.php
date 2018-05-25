<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return ($n % 10 != 1 || $n % 100 == 11);;
}}
;
$a->strings["Geonames settings updated."] = "";
$a->strings["Geonames Settings"] = "";
$a->strings["Enable Geonames Addon"] = "";
$a->strings["Submit"] = "Senda inn";
