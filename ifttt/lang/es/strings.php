<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["IFTTT Mirror"] = "IFTTT Espejo";
$a->strings["Body for \"new status message\""] = "Cuerpo de \"Nuevo mensaje de estatus\"";
$a->strings["Body for \"new photo upload\""] = "Cuerpo de \"nueva foto a subir\"";
$a->strings["Body for \"new link post\""] = "Cuerpo de \"Nuevo vinculo de artículo\"";
$a->strings["Generate new key"] = "Generar clave nueva";
$a->strings["Save Settings"] = "Guardar ajustes";
