<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Post to libertree"] = "Postați pe libertree";
$a->strings["libertree Post Settings"] = "Configurări Postări libertree ";
$a->strings["Enable Libertree Post Addon"] = "Activare Modul Postare Libertree";
$a->strings["Libertree API token"] = "Token API Libertree";
$a->strings["Libertree site URL"] = "URL site Libertree";
$a->strings["Post to Libertree by default"] = "Postați implicit pe Libertree";
$a->strings["Submit"] = "Trimite";
