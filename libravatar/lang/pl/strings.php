<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3"] = "Nie można zainstalować Libravatar pomyślnie. <br>Wymaga PHP> = 5.3";
$a->strings["generic profile image"] = "ogólny obraz profilu";
$a->strings["random geometric pattern"] = "losowy wzór geometryczny";
$a->strings["monster face"] = "twarz potwora";
$a->strings["computer generated face"] = "wygenerowane komputerowo twarz";
$a->strings["retro arcade style face"] = "twarz w stylu retro arcade";
$a->strings["Warning"] = "Ostrzeżenie";
$a->strings["Your PHP version %s is lower than the required PHP >= 5.3."] = "Twoja wersja PHP %s jest niższa niż wymagany PHP> = 5.3.";
$a->strings["This addon is not functional on your server."] = "Ten dodatek nie działa na twoim serwerze.";
$a->strings["Information"] = "Informacja";
$a->strings["Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar."] = "Dodatek Gravatar jest zainstalowany. Wyłącz dodatek Gravatar. Dodatek Libravatar powróci do Gravatar, jeśli w Libravatar nie zostanie znaleziony żaden przedmiot.";
$a->strings["Submit"] = "Wyślij";
$a->strings["Default avatar image"] = "Domyślny obraz awatara";
$a->strings["Select default avatar image if none was found. See README"] = "Wybierz domyślny obraz awatara, jeśli nie został znaleziony. Zobacz README";
$a->strings["Libravatar settings updated."] = "Zaktualizowano ustawienia Libravatar.";
