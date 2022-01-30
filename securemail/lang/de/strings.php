<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Secure Mail'] = '"Secure Mail" aktivieren';
$a->strings['Public key'] = 'Öffentlicher Schlüssel';
$a->strings['Your public PGP key, ascii armored format'] = 'Dein öffentlicher PGP Schlüssel, im ASCII-Format';
$a->strings['"Secure Mail" Settings'] = '"Secure Mail"-Einstellungen';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Save and send test'] = 'Test speichern und senden';
$a->strings['Test email sent'] = 'Test-E-Mail gesendet';
$a->strings['There was an error sending the test email'] = 'Es gab ein Fehler beim Senden der Test-E-Mail';
