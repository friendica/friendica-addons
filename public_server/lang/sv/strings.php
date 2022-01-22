<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Administrator'] = 'Administratör';
$a->strings['Your Friendica account is about to expire.'] = 'Ditt Friendica-konto är på väg att sluta gälla.';
