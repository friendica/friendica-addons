<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Post to libertree'] = 'Publier sur libertree';
$a->strings['libertree Export'] = 'Export de Libertree';
$a->strings['Enable Libertree Post Addon'] = 'Activer l\'extension de publication Libertree';
$a->strings['Libertree API token'] = 'Clé de l\'API libertree';
$a->strings['Libertree site URL'] = 'URL du site libertree';
$a->strings['Post to Libertree by default'] = 'Publier sur libertree par défaut';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
