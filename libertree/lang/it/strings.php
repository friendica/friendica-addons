<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to libertree"] = "Invia a Libertree";
$a->strings["libertree Post Settings"] = "Impostazioni di invio a Libertree";
$a->strings["Enable Libertree Post Addon"] = "Abilita il componente aggiuntivo di invio a Libertree";
$a->strings["Libertree API token"] = "Token API Libertree";
$a->strings["Libertree site URL"] = "Indirizzo sito Libertree";
$a->strings["Post to Libertree by default"] = "Invia sempre a Libertree";
$a->strings["Submit"] = "Invia";
