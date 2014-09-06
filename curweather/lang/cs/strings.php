<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Current Weather"] = "Aktuální počasí";
$a->strings["Current Weather settings updated."] = "Nastavení pro Aktuální počasí aktualizováno.";
$a->strings["Weather Location: "] = "Poloha počasí:";
$a->strings["Enable Current Weather"] = "Povolit Aktuální počasí";
$a->strings["Save Settings"] = "Uložit Nastavení";
