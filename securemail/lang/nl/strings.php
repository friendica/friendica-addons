<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"Secure Mail\" Settings"] = "\"Secure Mail\" instellingen";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Enable Secure Mail"] = "Secure Mail inschakelen";
$a->strings["Secure Mail Settings saved."] = "Secure Mail instellingen opgeslagen";
