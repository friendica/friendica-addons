<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Home page to load after login  - leave blank for profile wall'] = 'Page d\'accueil à charger après authentification - laisser ce champ vide pour charger votre mur';
$a->strings['Examples: "network" or "notifications/system"'] = 'Exemples : "network" ou "notifications/system"';
$a->strings['Startpage'] = 'Startpage';
