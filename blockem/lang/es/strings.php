<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Blockem'] = 'Blockem (Bloquealos)';
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Oculta el contenido del usuario al colapsar las publicaciones. También reemplaza su avatar con una imagen genérica.';
$a->strings['Comma separated profile URLS:'] = 'URLs de perfil separadas por comas:';
$a->strings['Save Settings'] = 'Guardar configuración';
$a->strings['Filtered user: %s'] = 'Usuario filtrado: %s';
$a->strings['Unblock Author'] = 'Desbloquear autor';
$a->strings['Block Author'] = 'Bloquear autor';
