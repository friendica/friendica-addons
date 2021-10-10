<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['From Address'] = 'Depuis l\'adresse';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Re:'] = 'Re :';
$a->strings['Friendica post'] = 'Message Friendica';
$a->strings['Diaspora post'] = 'Message Diaspora';
$a->strings['Email'] = 'Courriel';
$a->strings['Friendica Item'] = 'Élément de Friendica';
$a->strings['Local'] = 'Local';
$a->strings['Email Address'] = 'Adresse de courriel';
$a->strings['Enabled'] = 'Activer';
$a->strings['Mail Stream Settings'] = 'Paramètres de Mail Stream';
