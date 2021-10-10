<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['Post to Insanejournal'] = 'Postare pe Insanejournal';
$a->strings['InsaneJournal Post Settings'] = 'Configurări Postări Insaneournal';
$a->strings['Enable InsaneJournal Post Addon'] = 'Activare Modul Postare InsaneJournal';
$a->strings['InsaneJournal username'] = 'Utilizator InsaneJournal ';
$a->strings['InsaneJournal password'] = 'Parolă InsaneJournal ';
$a->strings['Post to InsaneJournal by default'] = 'Postați implicit pe InsaneJournal ';
$a->strings['Submit'] = 'Trimite';
