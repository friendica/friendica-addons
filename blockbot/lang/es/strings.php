<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['Allow "good" crawlers'] = 'Permitir rastreadores web "buenos"';
$a->strings['Block GabSocial'] = 'Bloquear GabSocial';
$a->strings['Training mode'] = 'Modo de entrenamiento';
$a->strings['Settings updated.'] = 'Ajustes actualizados.';
