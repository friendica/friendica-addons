<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"pageheader\" Settings"] = "Ajustes de \"pageheader\"";
$a->strings["Submit"] = "Enviar";
$a->strings["pageheader Settings saved."] = "Ajustes de pageheader actualizados.";
