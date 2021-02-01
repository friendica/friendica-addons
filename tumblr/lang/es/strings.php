<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Permission denied."] = "Permiso denegado.";
$a->strings["You are now authenticated to tumblr."] = "Está ahora autenticado en tumblr.";
$a->strings["return to the connector page"] = "Vuelva a la página del conector";
$a->strings["Post to Tumblr"] = "Publicar en Tumblr";
$a->strings["Tumblr Post Settings"] = "Ajustes de publicación de Tumblr";
$a->strings["(Re-)Authenticate your tumblr page"] = "(Re-)autenticar su página de tumblr";
$a->strings["Enable Tumblr Post Addon"] = "Habilite el addon Tumblr Post";
$a->strings["Post to Tumblr by default"] = "Publique en Tumblr por defecto";
$a->strings["Post to page:"] = "Publicar en página:";
$a->strings["You are not authenticated to tumblr"] = "No está autenticado en tumblr";
$a->strings["Submit"] = "Enviar";
