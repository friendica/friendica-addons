<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to LiveJournal'] = 'Publier sur LiveJournal';
$a->strings['Enable LiveJournal Post Addon'] = 'Activer l\'extension LiveJournal';
$a->strings['LiveJournal username'] = 'Nom d\'utilisateur LiveJournal';
$a->strings['LiveJournal password'] = 'Mot de passe LiveJournal';
$a->strings['Post to LiveJournal by default'] = 'Publier sur LiveJournal par défaut';
$a->strings['LiveJournal Export'] = 'Export LiveJournal';
