<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Post to Wordpress"] = "Opublikuj w Wordpress";
$a->strings["Wordpress Export"] = "Eksport do Wordpress";
$a->strings["Enable WordPress Post Addon"] = "Włącz dodatek Dodaj post do WordPress";
$a->strings["WordPress username"] = "Nazwa użytkownika WordPress";
$a->strings["WordPress password"] = "Hasło WordPress";
$a->strings["WordPress API URL"] = "WordPress API URL";
$a->strings["Post to WordPress by default"] = "Wyślij do WordPress domyślnie";
$a->strings["Provide a backlink to the Friendica post"] = "Podaj zwrotny link do posta Friendica";
$a->strings["Text for the backlink, e.g. Read the original post and comment stream on Friendica."] = "Tekst linku zwrotnego, np. Przeczytaj oryginalny post i strumień komentarzy na stronie Friendica.";
$a->strings["Don't post messages that are too short"] = "Nie publikuj zbyt krótkich wiadomości";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Read the orig­i­nal post and com­ment stream on Friendica"] = "Przeczytaj oryginalny post i strumień komentarzy na stronie Friendica";
$a->strings["Post from Friendica"] = "Post z Friendica";
