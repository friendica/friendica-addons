<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to libertree"] = "Publicar en Libertree";
$a->strings["libertree Export"] = "libertree Exportar";
$a->strings["Enable Libertree Post Addon"] = "Habilitar Plugin de publicación de Libertree";
$a->strings["Libertree API token"] = "Símbolo de API de Libertree";
$a->strings["Libertree site URL"] = "URL de la página de Libertree";
$a->strings["Post to Libertree by default"] = "Publicar en Libertree por defecto";
$a->strings["Save Settings"] = "Guardar Ajustes";
