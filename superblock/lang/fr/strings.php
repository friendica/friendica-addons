<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Comma separated profile URLs to block'] = 'Liste d\'URLs de profils à bloquer séparées par des virgules';
$a->strings['Block Completely'] = 'Bloquer complètement';
