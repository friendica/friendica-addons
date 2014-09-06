<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["\"pageheader\" Settings"] = "Nastavení záhlaví stránky";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["pageheader Settings saved."] = "Nastavení záhlaví stránky uloženo.";
