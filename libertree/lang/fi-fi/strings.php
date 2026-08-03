<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to libertree'] = 'Lähetä Libertreehin';
$a->strings['Enable Libertree Post Addon'] = 'Ota Libertree -viestilisäosa käyttöön';
$a->strings['Libertree site URL'] = 'Libertree -sivuston URL-osoite';
$a->strings['Post to Libertree by default'] = 'Lähetä Libertreehin oletuksena';
