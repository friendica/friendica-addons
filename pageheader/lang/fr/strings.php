<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['"pageheader" Settings'] = 'Paramètres de la page d\'en-tête';
$a->strings['Message'] = 'Message';
$a->strings['Message to display on every page on this server (or put a pageheader.html file in your docroot)'] = 'Message à publier sur toutes les pages de ce serveur (ou bien mettez un fichier pageheader.html dans votre docroot)';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
