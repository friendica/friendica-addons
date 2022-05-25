<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable Markdown parsing'] = 'Activer l\'interprétation Markdown';
$a->strings['If enabled, adds Markdown support to the Compose Post form.'] = 'Si activé, ajoute le support de Markdown au formulaire de création d\'une publication.';
$a->strings['Markdown Settings'] = 'Paramètres de Markdown';
