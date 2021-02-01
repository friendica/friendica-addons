<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Numfriends settings updated."] = "Ajustes de Numfriends actualizados.";
$a->strings["Numfriends Settings"] = "Ajustes de Numfriends";
$a->strings["How many contacts to display on profile sidebar"] = "Cuántos contactos mostrar en la barra lateral del perfil";
$a->strings["Submit"] = "Enviar";
