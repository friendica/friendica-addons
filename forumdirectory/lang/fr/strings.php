<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Forum Directory'] = 'Annuaire de Forums';
$a->strings['Public access denied.'] = 'Accès public refusé.';
$a->strings['No entries (some entries may be hidden).'] = 'Pas de résultats (certains résultats peuvent être cachés).';
$a->strings['Global Directory'] = 'Annuaire Global';
$a->strings['Find on this site'] = 'Trouver sur cette instance';
$a->strings['Results for:'] = 'Résultats pour :';
$a->strings['Find'] = 'Chercher';
