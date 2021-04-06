<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"Show more\" Settings"] = "Ajustes de \"Mostrar más\"";
$a->strings["Enable Show More"] = "Habilitar Mostrar Más";
$a->strings["Cutting posts after how much characters"] = "Cortar entradas después de tantos carácteres";
$a->strings["Save Settings"] = "Guardar ajustes";
$a->strings["show more"] = "mostrar más";
