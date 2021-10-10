<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New Member'] = 'Uusi jäsen';
$a->strings['Tips for New Members'] = 'Vinkkejä uusille käyttäjille';
$a->strings['Global Support Forum'] = 'Maailmanlaajuinen tukifoorumi';
$a->strings['Local Support Forum'] = 'Paikallinen tukifoorumi';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Message'] = 'Viesti';
$a->strings['Add a link to global support forum'] = 'Lisää linkki maailmanlaajuiseen tukifoorumiin';
$a->strings['Add a link to the local support forum'] = 'Lisää linkki paikalliseen tukifoorumiin';
$a->strings['Name of the local support group'] = 'Paikallisen tukifoorumin nimi';
