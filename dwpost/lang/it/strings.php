<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Dreamwidth'] = 'Invia a Dreamwidth';
$a->strings['Enable Dreamwidth Post Addon'] = 'Abilita il componente aggiuntivo di pubblicazione Dreamwidth';
$a->strings['Dreamwidth username'] = 'Nome utente Dreamwidth';
$a->strings['Dreamwidth password'] = 'Password Dreamwidth';
$a->strings['Post to Dreamwidth by default'] = 'Pubblica su dreamwidth per impostazione predefinita';
$a->strings['Dreamwidth Export'] = 'Esporta Dreamwidth';
