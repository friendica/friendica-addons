<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Language Filter'] = 'Kielisuodatin';
$a->strings['Use the language filter'] = 'Ota kielisuodatin käyttöön';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Language Filter Settings saved.'] = 'Kielisuodatinasetukset tallennettu';
$a->strings['Filtered language: %s'] = 'Suodatettu kieli: %s';
