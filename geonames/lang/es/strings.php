<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Geonames Settings'] = 'Ajustes de Geonombres';
$a->strings['Replace numerical coordinates by the nearest populated location name in your posts.'] = 'Reemplace las coordenadas numéricas por el nombre de la ubicación poblada más cercana en sus publicaciones.';
$a->strings['Enable Geonames Addon'] = 'Habilitar Plugin de Geonombres';
$a->strings['Save Settings'] = 'Guardar Ajustes';
