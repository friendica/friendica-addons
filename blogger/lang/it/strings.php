<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to blogger'] = 'Invia a Blogger';
$a->strings['Blogger Export'] = 'Esporta Blogger';
$a->strings['Enable Blogger Post Addon'] = 'Abilita il componente aggiuntivo di invio a Blogger';
$a->strings['Blogger username'] = 'Nome utente Blogger';
$a->strings['Blogger password'] = 'Password Blogger';
$a->strings['Blogger API URL'] = 'Indirizzo API Blogger';
$a->strings['Post to Blogger by default'] = 'Invia sempre a Blogger';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Post from Friendica'] = 'Messaggio da Friendica';
