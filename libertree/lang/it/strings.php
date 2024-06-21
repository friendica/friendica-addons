<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to libertree'] = 'Invia a Libertree';
$a->strings['Enable Libertree Post Addon'] = 'Abilita il componente aggiuntivo di invio a Libertree';
$a->strings['Libertree site URL'] = 'Indirizzo sito Libertree';
$a->strings['Libertree API token'] = 'Token API Libertree';
$a->strings['Post to Libertree by default'] = 'Invia sempre a Libertree';
$a->strings['Libertree Export'] = 'Esporta Libertree';
