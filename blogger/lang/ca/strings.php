<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to blogger'] = 'Publicar a blogger';
$a->strings['Blogger Export'] = 'Exportació de Blogger';
$a->strings['Enable Blogger Post Addon'] = 'Habilita Addon Post de Blogger';
$a->strings['Blogger username'] = 'Nom d\'usuari de Blogger';
$a->strings['Blogger password'] = 'Contrasenya de Blogger';
$a->strings['Blogger API URL'] = 'URL de l\'API de Blogger';
$a->strings['Post to Blogger by default'] = 'Publica a Blogger de manera predeterminada';
$a->strings['Save Settings'] = 'Desa la configuració';
$a->strings['Post from Friendica'] = 'Publica de Friendica';
