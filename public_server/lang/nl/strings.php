<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Administrator'] = 'Beheerder';
$a->strings['Your account on %s will expire in a few days.'] = 'Uw account op %s zal over enkele dagen vervallen';
