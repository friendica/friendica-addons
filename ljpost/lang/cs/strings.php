<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Post to LiveJournal'] = 'Odeslat na LiveJournal';
$a->strings['Enable LiveJournal Post Addon'] = 'Povolit doplněk LiveJournal Post';
$a->strings['LiveJournal username'] = 'LiveJournal uživatelské jméno';
$a->strings['LiveJournal password'] = 'LiveJournal heslo';
$a->strings['Post to LiveJournal by default'] = 'Defaultně umístit na LiveJournal';
