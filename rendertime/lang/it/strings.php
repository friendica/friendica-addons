<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s"] = "Database: %s/%s, Rete: %s, Rendering: %s, Sessione: %s, I/O: %s, Altro: %s, Totale: %s";
$a->strings["Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s"] = "Class-Init: %s, Boot: %s, Init: %s, Content: %s, Altro: %s, Totale: %s";
