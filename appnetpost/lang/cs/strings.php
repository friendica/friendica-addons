<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Post to app.net"] = "Poslat příspěvek na app.net";
$a->strings["App.net Export"] = "App.net Export";
$a->strings["Enable App.net Post Plugin"] = "Aktivovat App.net Post Plugin";
$a->strings["Post to App.net by default"] = "Defaultně poslat na App.net";
$a->strings["Save Settings"] = "Uložit Nastavení";
