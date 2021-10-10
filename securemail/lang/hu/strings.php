<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"Secure Mail" Settings'] = 'Biztonságos levél beállításai';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Save and send test'] = 'Mentés és teszt küldése';
$a->strings['Enable Secure Mail'] = 'Biztonságos levél engedélyezése';
$a->strings['Public key'] = 'Nyilvános kulcs';
$a->strings['Your public PGP key, ascii armored format'] = 'A nyilvános PGP kulcsa ASCII-védett formátumban';
$a->strings['Test email sent'] = 'Tesztlevél elküldve';
$a->strings['There was an error sending the test email'] = 'Hiba történt a tesztlevél küldésekor';
