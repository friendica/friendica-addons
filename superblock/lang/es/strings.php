<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Superblock"] = "Soperbloquéo";
$a->strings["Comma separated profile URLS to block"] = "Perfil de URLS a bloque separado por comas";
$a->strings["Save Settings"] = "Guardar configuración";
$a->strings["Block Completely"] = "Bloquear completamente";
