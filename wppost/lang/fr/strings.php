<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Wordpress'] = 'Publier sur Wordpress';
$a->strings['Enable Wordpress Post Addon'] = 'Activer l\'extension WordPress';
$a->strings['Wordpress username'] = 'Nom d\'utilisateur WordPress';
$a->strings['Wordpress password'] = 'Mot de passe WordPress';
$a->strings['WordPress API URL'] = 'URL de l\'API WordPress';
$a->strings['Post to Wordpress by default'] = 'Publier sur WordPress par défaut';
$a->strings['Don\'t post messages that are too short'] = 'Ne pas publier de message trop court';
$a->strings['Wordpress Export'] = 'Export WordPress';
$a->strings['Post from Friendica'] = 'Publier depuis Friendica';
