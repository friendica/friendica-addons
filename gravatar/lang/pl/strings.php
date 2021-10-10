<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['generic profile image'] = 'ogólny obraz profilu';
$a->strings['random geometric pattern'] = 'losowy wzór geometryczny';
$a->strings['monster face'] = 'twarz potwora';
$a->strings['computer generated face'] = 'wygenerowana komputerowo twarz';
$a->strings['retro arcade style face'] = 'twarz w stylu retro arcade';
$a->strings['Information'] = 'Informacje';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Zainstalowany jest także dodatek Libravatar. Wyłącz dodatek Libravatar lub dodatek Gravatar.<br> Dodatek Libravatar powróci do Gravatar, jeśli w Libravatar nie zostanie znaleziony żaden przedmiot.';
$a->strings['Submit'] = 'Zatwierdź';
$a->strings['Default avatar image'] = 'Domyślny obraz awatara';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Wybierz domyślny obraz awatara, jeśli nie znaleziono go w Gravatar. Zobacz README';
$a->strings['Rating of images'] = 'Ocena zdjęć';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Wybierz odpowiednią ocenę awatara dla swojej witryny. Zobacz README';
$a->strings['Gravatar settings updated.'] = 'Zaktualizowano ustawienia Gravatar.';
