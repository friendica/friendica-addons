<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"Secure Mail" Settings'] = 'Secure Mail -asetukset';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Save and send test'] = 'Tallenna ja lähetä koeviesti';
$a->strings['Enable Secure Mail'] = 'Ota Secure Mail käyttöön';
$a->strings['Public key'] = 'Julkinen avain';
$a->strings['Secure Mail Settings saved.'] = 'Secure Mail -asetukset tallennettu.';
$a->strings['Test email sent'] = 'Koeviesti lähetetty';
$a->strings['There was an error sending the test email'] = 'Testisähköpostin lähetyksessä tapahtui virhe';
