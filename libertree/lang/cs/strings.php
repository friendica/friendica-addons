<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Post to libertree'] = 'Poslat na libertree';
$a->strings['Enable Libertree Post Addon'] = 'Povolit doplněk Libertree Post';
$a->strings['Libertree site URL'] = 'URL adresa Libertree ';
$a->strings['Libertree API token'] = 'Libertree API token';
$a->strings['Post to Libertree by default'] = 'Ve výchozím stavu posílat na Libertree';
