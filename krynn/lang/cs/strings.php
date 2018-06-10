<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Krynn Settings"] = "Nastavení Krynn";
$a->strings["Enable Krynn Addon"] = "Povolit doplněk Krynn";
$a->strings["Submit"] = "Odeslat";
