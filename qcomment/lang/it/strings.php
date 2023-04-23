<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings[':-)'] = ':-)';
$a->strings[':-('] = ':-(';
$a->strings['lol'] = 'lol';
$a->strings['Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies.'] = 'Trovi i commenti rapidi vicino al box dei commenti, a volte nascosti. Cliccali per inviare semplici risposte.';
$a->strings['Enter quick comments, one per line'] = 'Inserire un commento rapido, uno per linea';
$a->strings['Quick Comment Settings'] = 'Impostazioni commento rapido';
