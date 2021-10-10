<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Performance: Database: %s, Network: %s, Rendering: %s, Parser: %s, I/O: %s, Other: %s, Total: %s'] = 'Performances: Base de données : %s, Réseau : %s, Rendu : %s, Parser : %s, Entrées/sorties : %s, Autre : %s, Total : %s';
