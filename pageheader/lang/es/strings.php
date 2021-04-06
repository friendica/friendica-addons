<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"pageheader\" Settings"] = "Ajustes de \"pageheader\"";
$a->strings["Message"] = "Mensaje";
$a->strings["Message to display on every page on this server (or put a pageheader.html file in your docroot)"] = "Mensaje para mostrar en todas las páginas de este servidor (o poner un archivo pageheader.html en su docroot)";
$a->strings["Save Settings"] = "Guardar ajustes";
