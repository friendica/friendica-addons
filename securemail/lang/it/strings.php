<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"Secure Mail" Settings'] = 'Impostazioni Secure Mail';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Save and send test'] = 'Salva e invia mail di prova';
$a->strings['Enable Secure Mail'] = 'Abilita Secure Mail';
$a->strings['Public key'] = 'Chiave pubblica';
$a->strings['Your public PGP key, ascii armored format'] = 'La tua chiave pubblica PGP, in formato ascii armored';
$a->strings['Secure Mail Settings saved.'] = 'Impostazioni Secure Mail salvate.';
$a->strings['Test email sent'] = 'Email di prova invata';
$a->strings['There was an error sending the test email'] = 'Si è verificato un errore durante l\'invio dell\'email di prova';
