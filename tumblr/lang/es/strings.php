<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Permission denied.'] = 'Permiso denegado.';
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['Consumer Key'] = 'Consumer Key';
$a->strings['Consumer Secret'] = 'Consumer Secret';
$a->strings['Post to page:'] = 'Publicar en página:';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Re-)autenticar su página de tumblr';
$a->strings['You are not authenticated to tumblr'] = 'No está autenticado en tumblr';
$a->strings['Enable Tumblr Post Addon'] = 'Habilitar el complemento de publicación de Tumblr';
$a->strings['Post to Tumblr by default'] = 'Publique en Tumblr por defecto';
$a->strings['Post to Tumblr'] = 'Publicar en Tumblr';
