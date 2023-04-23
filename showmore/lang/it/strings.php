<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable Show More'] = 'Abilita "Mostra altro"';
$a->strings['Cutting posts after how many characters'] = 'Taglia messaggi dopo quanti caratteri';
$a->strings['"Show more" Settings'] = 'Impostazioni "Mostra altro"';
$a->strings['show more'] = 'mostra di più';
