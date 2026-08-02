<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Submit'] = 'Lähetä';
$a->strings['Default zoom'] = 'Oletuszoomaus';
$a->strings['The default zoom level. (1:world, 18:highest)'] = 'Oletuszoomaustaso (1: kaukaisin, 18: läheisin)';
$a->strings['Settings updated.'] = 'Asetukset päivitetty';
