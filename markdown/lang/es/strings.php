<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Markdown"] = "Markdown";
$a->strings["Enable Markdown parsing"] = "Habilitar el análisis de Markdown";
$a->strings["Save Settings"] = "Guardar ajustes";
