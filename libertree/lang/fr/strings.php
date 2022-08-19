<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to libertree'] = 'Publier sur libertree';
$a->strings['Enable Libertree Post Addon'] = 'Activer l\'extension de publication Libertree';
$a->strings['Libertree site URL'] = 'URL du site libertree';
$a->strings['Libertree API token'] = 'Clé de l\'API libertree';
$a->strings['Post to Libertree by default'] = 'Publier sur libertree par défaut';
$a->strings['Libertree Export'] = 'Export Libertree';
