<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Allow \"good\" crawlers"] = "Povolit „dobré“ crawlery";
$a->strings["Block GabSocial"] = "Zablokovat GabSocial";
$a->strings["Training mode"] = "Trénovací režim";
$a->strings["Settings updated."] = "Nastavení aktualizována.";
