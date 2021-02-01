<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to LiveJournal"] = "Publicar en LiveJournal";
$a->strings["LiveJournal Post Settings"] = "Ajustes de publicación de LiveJournal";
$a->strings["Enable LiveJournal Post Addon"] = "Habilitar Addon de publicción de LiveJournal";
$a->strings["LiveJournal username"] = "Nombre de usuario de LiveJournal";
$a->strings["LiveJournal password"] = "Contraseña de LiveJournal";
$a->strings["Post to LiveJournal by default"] = "Publicar en LiveJournal por defecto";
$a->strings["Submit"] = "Enviar";
