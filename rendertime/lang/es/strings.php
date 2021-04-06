<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s"] = "Base de Datos: %s/%s, Red: %s, Reproducción: %s, Sesión: %s, E/S: %s, Otros: %s, Total: %s";
$a->strings["Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s"] = "Clase de Inicio: %s, Arranque: %s, Inicio: %s, Contenido: %s, Otros: %s, Total: %s";
