<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n==1)) { return 0; } else if (($n>=2 && $n<=4)) { return 1; } else  { return 2; }
}}
$a->strings['Post to LiveJournal'] = 'Poslat na LiveJournal';
$a->strings['LiveJournal Post Settings'] = 'Nastavení LiveJournal Post';
$a->strings['Enable LiveJournal Post Addon'] = 'Povolit LiveJournal Post addon';
$a->strings['LiveJournal username'] = 'LiveJournal uživatelské jméno';
$a->strings['LiveJournal password'] = 'LiveJournal heslo';
$a->strings['Post to LiveJournal by default'] = 'Defaultně umístit na LiveJournal';
$a->strings['Submit'] = 'Odeslat';
