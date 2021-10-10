<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Group Text'] = 'Groupe de texte';
$a->strings['Use a text only (non-image) group selector in the "group edit" menu'] = 'Utiliser uniquement un groupe de texte (pas d\'image) dans le menu "groupedit"';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
