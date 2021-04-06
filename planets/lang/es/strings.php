<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Planets"] = "Planets";
$a->strings["Planets Settings"] = "Ajustes de Planets";
$a->strings["Enable Planets Addon"] = "Habilitar Addon/plugin Planets";
$a->strings["Save Settings"] = "Grabar ajustes";
