<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n==1)) { return 0; } else if (($n>=2 && $n<=4)) { return 1; } else  { return 2; }
}}
;
$a->strings["No Timeline settings updated."] = "Nastavení No Timeline aktualizováno.";
$a->strings["No Timeline Settings"] = "Nastavení No Timeline";
$a->strings["Disable Archive selector on profile wall"] = "Znemožnit použití archivu na této profilové zdi.";
$a->strings["Submit"] = "Odeslat";
