<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n==1)) { return 0; } else if (($n>=2 && $n<=4)) { return 1; } else  { return 2; }
}}
;
$a->strings["Post to libertree"] = "Poslat na libertree";
$a->strings["libertree Post Settings"] = "libertree nastavení příspěvků";
$a->strings["Enable Libertree Post Addon"] = "Povolit Libertree Post rozšíření";
$a->strings["Libertree API token"] = "Libertree API token";
$a->strings["Libertree site URL"] = "URL adresa Libertree ";
$a->strings["Post to Libertree by default"] = "Defaultně poslat na Libertree";
$a->strings["Submit"] = "Odeslat";
