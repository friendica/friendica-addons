<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['You won!'] = 'Vous avez gagné !';
$a->strings['I won!'] = 'J’ai gagné !';
