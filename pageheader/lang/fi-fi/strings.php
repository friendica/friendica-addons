<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"pageheader" Settings'] = '"pageheader" -asetukset';
$a->strings['Message'] = 'Viesti';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['pageheader Settings saved.'] = 'pageheader -asetukset tallennettu.';
