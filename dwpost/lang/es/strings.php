<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Dreamwidth"] = "Publicar en Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Ajustes de publicación de Dreamwidth";
$a->strings["Enable dreamwidth Post Addon"] = "Activar el addon de publicación dreamwidth";
$a->strings["dreamwidth username"] = "Nombre de usuario de dreamwidth";
$a->strings["dreamwidth password"] = "Contraseña de dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Publicar en dreamwidth por defecto";
$a->strings["Submit"] = "Enviar";
