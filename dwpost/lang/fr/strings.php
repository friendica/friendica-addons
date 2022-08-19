<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Dreamwidth'] = 'Publier sur Dreamwidth';
$a->strings['Enable Dreamwidth Post Addon'] = 'Activer l\'extension Dreamwidth';
$a->strings['Dreamwidth username'] = 'Nom d\'utilisateur Dreamwidth';
$a->strings['Dreamwidth password'] = 'Mot de passe Dreamwidth';
$a->strings['Post to Dreamwidth by default'] = 'Publier sur Dreamwidth par défaut';
$a->strings['Dreamwidth Export'] = 'Export Dreamwidth';
