<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Cache le contenu de l\'utilisateur en contractant les publications. Remplace aussi leur avatar par une image générique.';
$a->strings['Comma separated profile URLS:'] = 'URLs de profil séparées par des virgules:';
$a->strings['Blockem'] = 'Bloquez-les';
$a->strings['Filtered user: %s'] = 'Utilisateur filtré:%s';
$a->strings['Unblock Author'] = 'Débloquer l\'Auteur';
$a->strings['Block Author'] = 'Bloquer l\'Auteur';
