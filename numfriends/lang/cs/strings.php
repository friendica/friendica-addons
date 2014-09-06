<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Numfriends settings updated."] = "Numfriends nastavení aktualizováno";
$a->strings["Numfriends Settings"] = "Nastavení Numfriends";
$a->strings["How many contacts to display on profile sidebar"] = "Kolik kontaktů zobrazit na profilovém bočním menu";
$a->strings["Save Settings"] = "Uložit Nastavení";
