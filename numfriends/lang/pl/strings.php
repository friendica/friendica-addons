<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Numfriends settings updated.'] = 'Zaktualizowano ustawienia Numfriends.';
$a->strings['Numfriends Settings'] = 'Ustawienia Numfriends';
$a->strings['How many contacts to display on profile sidebar'] = 'Ile kontaktów można wyświetlić na pasku bocznym profilu';
$a->strings['Submit'] = 'Wyślij';
