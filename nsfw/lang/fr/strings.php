<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable Content filter'] = 'Activer le filtrage de contenu';
$a->strings['Comma separated list of keywords to hide'] = 'Liste de mots-clés - séparés par des virgules - à cacher';
$a->strings['Use /expression/ to provide regular expressions'] = 'Utilisez /expression/ pour les expressions rationnelles';
$a->strings['Content Filter (NSFW and more)'] = 'Filtre de contenu (NSFW et autres)';
$a->strings['Filtered tag: %s'] = 'Tag filtré: %s';
$a->strings['Filtered word: %s'] = 'Mot filtré: %s';
