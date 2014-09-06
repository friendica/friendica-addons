<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["YourLS"] = "YourLS";
$a->strings["URL: http://"] = "URL: http://";
$a->strings["Username:"] = "Uživatelské jméno:";
$a->strings["Password:"] = "Heslo: ";
$a->strings["Use SSL "] = "Použít SSL";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["yourls Settings saved."] = "yourls nastavení uloženo.";
