<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Numfriends settings updated."] = "Nastavení Numfriends aktualizováno";
$a->strings["Numfriends Settings"] = "Nastavení Numfriends";
$a->strings["How many contacts to display on profile sidebar"] = "Kolik kontaktů zobrazit na profilové postranní liště";
$a->strings["Submit"] = "Odeslat";
