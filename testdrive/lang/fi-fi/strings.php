<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Administrator'] = 'Ylläpitäjä';
$a->strings['Your account on %s will expire in a few days.'] = '%s -tilisi vanhenee muutaman päivän kuluttua.';
$a->strings['Your Friendica test account is about to expire.'] = 'Koetilisi Friendicassa umpeutuu kohta.';
