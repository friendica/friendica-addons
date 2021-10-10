<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['No Timeline Settings'] = 'No hay ajustes de Línea de Tiempo';
$a->strings['Disable Archive selector on profile wall'] = 'Deshabilitar el selector Archivo en el muro de perfil';
$a->strings['Save Settings'] = 'Grabar ajustes';
