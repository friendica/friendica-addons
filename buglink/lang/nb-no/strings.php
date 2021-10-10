<?php

if(! function_exists("string_plural_select_nb_no")) {
function string_plural_select_nb_no($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Report Bug'] = 'Rapporter feil';
