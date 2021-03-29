<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Randplace Settings"] = "Impostazioni \"Posizione casuale\"";
$a->strings["Enable Randplace Addon"] = "Abilita il componente aggiuntivo Posizione Casuale";
$a->strings["Save Settings"] = "Salva Impostazioni";
