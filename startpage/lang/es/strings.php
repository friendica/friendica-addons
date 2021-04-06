<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Startpage"] = "Página de Inicio";
$a->strings["Home page to load after login  - leave blank for profile wall"] = "Página principal a cargar tras el acceso - dejar en blanco para el muro de perfil";
$a->strings["Examples: &quot;network&quot; or &quot;notifications/system&quot;"] = "Ejemplos: &quot;network&quot; o &quot;notifications/system&quot;";
$a->strings["Save Settings"] = "Guardar ajustes";
