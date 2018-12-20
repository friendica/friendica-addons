<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["\"Show more\" Settings"] = "Nastavení \"Show more\"";
$a->strings["Enable Show More"] = "Povolit Show more";
$a->strings["Cutting posts after how much characters"] = "Oříznout příspěvky po zadaném množství znaků";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Show More Settings saved."] = "Nastavení \"Show more\" uložena.";
$a->strings["show more"] = "zobrazit více";
