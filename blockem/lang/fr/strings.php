<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Blockem'] = 'Bloquez-les';
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Cache le contenu de l\'utilisateur en contractant les publications. Remplace aussi leur avatar par une image générique.';
$a->strings['Comma separated profile URLS:'] = 'URLs de profil séparées par des virgules:';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['BLOCKEM Settings saved.'] = 'Paramètres Bloquez-les sauvegardés.';
$a->strings['Filtered user: %s'] = 'Utilisateur filtré:%s';
$a->strings['Unblock Author'] = 'Débloquer l\'Auteur';
$a->strings['Block Author'] = 'Bloquer l\'Auteur';
$a->strings['blockem settings updated'] = 'Réglages Blockem mis à jour.';
