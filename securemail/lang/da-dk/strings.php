<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Secure Mail'] = 'Slå sikker mail til';
$a->strings['Public key'] = 'Offentlig nøgle';
$a->strings['Your public PGP key, ascii armored format'] = 'Din offentlige PGP nøgle i "ascii armored" format';
$a->strings['"Secure Mail" Settings'] = '"Sikker mail" Indstillinger';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Save and send test'] = 'Gem og send test';
$a->strings['Test email sent'] = 'Testmail sendt';
$a->strings['There was an error sending the test email'] = 'Der skete en fejl da testmailen skulle sendes';
