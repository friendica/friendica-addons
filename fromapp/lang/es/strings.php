<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Fromapp settings updated."] = "Ajustes de Fromapp actualizados.";
$a->strings["FromApp Settings"] = "Ajustes de FromApp";
$a->strings["The application name you would like to show your posts originating from."] = "El nombre de la aplicación desde la que le gustaría que se mostrasen sus publicaciones.";
$a->strings["Use this application name even if another application was used."] = "Utilice este nombre de aplicación incluso si otra aplicación fue utilizada.";
$a->strings["Submit"] = "Enviar";
