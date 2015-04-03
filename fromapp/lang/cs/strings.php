<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Fromapp settings updated."] = "Fromapp nastavení aktualizováno.";
$a->strings["FromApp Settings"] = "FromApp nastavení";
$a->strings["The application name you would like to show your posts originating from."] = "Jméno zdrojové aplikace";
$a->strings["Use this application name even if another application was used."] = "Použij toto jméno aplikace i když byla použita jiná aplikace";
$a->strings["Submit"] = "Odeslat";
