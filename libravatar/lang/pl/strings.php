<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['generic profile image'] = 'ogólny obraz profilu';
$a->strings['random geometric pattern'] = 'losowy wzór geometryczny';
$a->strings['monster face'] = 'twarz potwora';
$a->strings['computer generated face'] = 'twarz wygenerowana komputerowo';
$a->strings['retro arcade style face'] = 'twarz w stylu retro arcade';
$a->strings['roboter face'] = 'twarz robota';
$a->strings['retro adventure game character'] = 'postać z gry przygodowej w stylu retro';
$a->strings['Information'] = 'Informacja';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Dodatek Gravatar jest zainstalowany. Wyłącz dodatek Gravatar. Dodatek Libravatar powróci do Gravatar, jeśli w Libravatar nie zostanie znaleziony żaden przedmiot.';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Default avatar image'] = 'Domyślny obraz awatara';
$a->strings['Select default avatar image if none was found. See README'] = 'Wybierz domyślny obraz awatara, jeśli nie został znaleziony. Zobacz README';
