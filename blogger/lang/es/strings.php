<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to blogger"] = "Entrada para blogger";
$a->strings["Blogger Export"] = "Esportar Blogger";
$a->strings["Enable Blogger Post Addon"] = "Habilitar el complemento de publicación de Blogger";
$a->strings["Blogger username"] = "Nombre de usuario de Blogger";
$a->strings["Blogger password"] = "Contraseña de Blogger";
$a->strings["Blogger API URL"] = "URL API de Blogger";
$a->strings["Post to Blogger by default"] = "Entrada a Blogger por defecto";
$a->strings["Save Settings"] = "Guardar ajustes";
$a->strings["Post from Friendica"] = "Entrada desde Friendica";
