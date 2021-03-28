<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Numfriends Settings"] = "Impostazioni Numfriends";
$a->strings["How many contacts to display on profile sidebar"] = "Quanti contatti visualizzare nella barra laterale del profilo";
$a->strings["Save Settings"] = "Salva Impostazioni";
