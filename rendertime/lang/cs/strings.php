<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Performance: Database: %s, Network: %s, Rendering: %s, Parser: %s, I/O: %s, Other: %s, Total: %s"] = "Výkonnost: Databáze: %s, Síť: %s, Rendering: %s, Parser: %s, I/O: %s, Ostatní: %s, Celkem: %s";
