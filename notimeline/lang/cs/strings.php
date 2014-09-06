<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["No Timeline settings updated."] = "Nastavení No Timeline aktualizováno.";
$a->strings["No Timeline Settings"] = "Nastavení No Timeline";
$a->strings["Disable Archive selector on profile wall"] = "Znemožnit použití archivu na této profilové zdi.";
$a->strings["Save Settings"] = "Uložit Nastavení";
