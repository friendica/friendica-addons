<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Secure Mail'] = 'Enable Secure Mail';
$a->strings['Public key'] = 'Public key';
$a->strings['Your public PGP key, ascii armored format'] = 'Your public PGP key, ASCII-armoured format';
$a->strings['"Secure Mail" Settings'] = 'Secure Mail settings';
$a->strings['Save Settings'] = 'Save settings';
$a->strings['Save and send test'] = 'Save and send test email';
$a->strings['Test email sent'] = 'Test email sent';
$a->strings['There was an error sending the test email'] = 'There was an error sending the test email';
