<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Fromapp settings updated."] = "Nastavení FromApp aktualizována.";
$a->strings["FromApp Settings"] = "Nastavení FromApp";
$a->strings["The application name you would like to show your posts originating from."] = "Jméno aplikace, která má být zobrazena jako zdroj Vašich příspěvků.";
$a->strings["Use this application name even if another application was used."] = "Použít toto jméno aplikace, i když byla použita jiná aplikace";
$a->strings["Submit"] = "Odeslat";
