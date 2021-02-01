<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Performance: Database: %s, Network: %s, Rendering: %s, Parser: %s, I/O: %s, Other: %s, Total: %s"] = "Cumplimiento: Base de datos: %s, Red: %s, Renderizado: %s, Analizador: %s, I/O: %s, Otro: %s, Total: %s";
