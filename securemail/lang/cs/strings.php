<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['"Secure Mail" Settings'] = 'Nastavení "Secure Mail"';
$a->strings['Save Settings'] = 'Uložit nastavení';
$a->strings['Save and send test'] = 'Uložit a poslat test';
$a->strings['Enable Secure Mail'] = 'Povolit Secure Mail';
$a->strings['Public key'] = 'Veřejný klíč';
$a->strings['Your public PGP key, ascii armored format'] = 'Váš veřejný klíč PGP ve formátu ASCII Armor';
$a->strings['Secure Mail Settings saved.'] = 'Nastavení Secure Mail uložena.';
$a->strings['Test email sent'] = 'Testovací e-mail odeslán';
$a->strings['There was an error sending the test email'] = 'Při odesílání testovacího e-mailu se vyskytla chyba';
