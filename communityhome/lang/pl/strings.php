<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Login"] = "Zaloguj się";
$a->strings["OpenID"] = "OpenID";
$a->strings["Latest users"] = "Najnowszy użytkownik";
$a->strings["Most active users"] = "Najbardziej aktywni użytkownicy";
$a->strings["Latest photos"] = "Najnowsze Zdjęcia";
$a->strings["Contact Photos"] = "Zdjęcia kontaktu";
$a->strings["Profile Photos"] = "Zdjęcie profilowe";
$a->strings["Latest likes"] = "Najnowsze polubienia";
$a->strings["event"] = "zdarzenie";
$a->strings["status"] = "status";
$a->strings["photo"] = "zdjęcie";
$a->strings["%1\$s likes %2\$s's %3\$s"] = "%1\$slubi %2\$s %3\$s ";
$a->strings["Welcome to %s"] = "Witamy w %s";
