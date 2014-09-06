<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["YourLS"] = "YourLS";
$a->strings["URL: http://"] = "URL: http://";
$a->strings["Username:"] = "Utilizator:";
$a->strings["Password:"] = "Parola:";
$a->strings["Use SSL "] = "Utilizează SSL";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["yourls Settings saved."] = "Configurările YourLS au fost salvate.";
