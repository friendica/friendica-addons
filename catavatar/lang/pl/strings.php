<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Set default profile avatar or randomize the cat.'] = 'Ustaw domyślny awatar profilu lub użyj losowego kota.';
$a->strings['Cat Avatar Settings'] = 'Ustawienia Kot Avatar';
$a->strings['Use Cat as Avatar'] = 'Użyj kota jako awatara';
$a->strings['Another random Cat!'] = 'Inny losowy kot!';
$a->strings['Reset to email Cat'] = 'Resetuj Kota na e-mail';
$a->strings['The cat hadn\'t found itself.'] = 'Kot się nie znalazł.';
$a->strings['There was an error, the cat ran away.'] = 'Wystąpił błąd, kot uciekł.';
$a->strings['Profile Photos'] = 'Zdjęcie profilowe';
$a->strings['Meow!'] = 'Miau!';
