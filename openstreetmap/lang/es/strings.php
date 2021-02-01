<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Submit"] = "Enviar";
$a->strings["Tile Server URL"] = "URL del mosaico de servidor";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "Una lista de <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">mosaico de servidores públicos</a>";
$a->strings["Default zoom"] = "Zoom por defecto";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "El nivel por defecto de zoom. (1:world, 18:highest)";
$a->strings["Settings updated."] = "Ajustes actualizados";
