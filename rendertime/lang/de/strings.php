<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Performance: Database: %s, Network: %s, Rendering: %s, Parser: %s, I/O: %s, Other: %s, Total: %s"] = "Performance: Datenbank: %s, Netzwerk: %s, Rendering: %s, Parser: %s, I/O: %s, Anderes: %s, Gesamt: %s";
