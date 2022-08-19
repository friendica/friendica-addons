<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable Krynn Addon'] = 'Activer l\'application complémentaire Krynn';
$a->strings['Krynn Settings'] = 'Paramètres de Krynn';
