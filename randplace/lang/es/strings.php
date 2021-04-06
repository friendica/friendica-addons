<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Randplace Settings"] = "Ajustes de Randplace";
$a->strings["Enable Randplace Addon"] = "Habilitar el complemento Randplace";
$a->strings["Save Settings"] = "Guardar ajustes";
