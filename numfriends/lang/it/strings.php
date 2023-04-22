<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['How many contacts to display on profile sidebar'] = 'Quanti contatti visualizzare nella barra laterale del profilo';
$a->strings['Numfriends Settings'] = 'Impostazioni Numfriends';
