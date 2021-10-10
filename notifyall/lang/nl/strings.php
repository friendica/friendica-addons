<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Send email to all members'] = 'Stuur e-mail naar alle leden';
$a->strings['%s Administrator'] = '%s Beheerder';
$a->strings['%1$s, %2$s Administrator'] = '%1$s%2$s Beheerder';
