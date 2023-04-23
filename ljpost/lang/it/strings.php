<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to LiveJournal'] = 'Invia a LiveJournal';
$a->strings['Enable LiveJournal Post Addon'] = 'Abilita il componente aggiuntivo di invio a LiveJournal';
$a->strings['LiveJournal username'] = 'Nome utente LiveJournal';
$a->strings['LiveJournal password'] = 'Password LiveJournal';
$a->strings['Post to LiveJournal by default'] = 'Invia sempre a LiveJournal';
$a->strings['LiveJournal Export'] = 'Esporta LiveJournal';
