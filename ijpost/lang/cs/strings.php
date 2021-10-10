<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Post to Insanejournal'] = 'Posílat na InsaneJournal';
$a->strings['InsaneJournal Post Settings'] = 'Nastavení InsaneJournal Post';
$a->strings['Enable InsaneJournal Post Addon'] = 'Povolit doplněk InsaneJournal Post';
$a->strings['InsaneJournal username'] = 'Uživatelské jméno InsaneJournal';
$a->strings['InsaneJournal password'] = 'Heslo InsaneJournal';
$a->strings['Post to InsaneJournal by default'] = 'Ve výchozím stavu posílat příspěvky na InsaneJournal';
$a->strings['Submit'] = 'Odeslat';
