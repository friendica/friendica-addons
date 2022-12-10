<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to Dreamwidth'] = 'Opublikuj w Dreamwidth';
$a->strings['Enable Dreamwidth Post Addon'] = 'Włącz dodatek Dreamwidth Post';
$a->strings['Dreamwidth username'] = 'Nazwa użytkownika Dreamwidth';
$a->strings['Dreamwidth password'] = 'Hasło Dreamwidth';
$a->strings['Post to Dreamwidth by default'] = 'Publikuj domyślnie w serwisie Dreamwidth';
$a->strings['Dreamwidth Export'] = 'Eksport do Dreamwidth';
