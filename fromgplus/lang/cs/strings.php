<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Google+ Mirror"] = "Google+ Mirror";
$a->strings["Enable Google+ Import"] = "Povolit Import z Google+";
$a->strings["Google Account ID"] = "název účtu Google ";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Google+ Import Settings saved."] = "Nastavení importu z Google+ uloženo.";
