<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Post to LiveJournal"] = "Opublikuj w LiveJournal";
$a->strings["LiveJournal Post Settings"] = "Ustawienia Postów LiveJournal";
$a->strings["Enable LiveJournal Post Addon"] = "Włącz dodatek LiveJournal";
$a->strings["LiveJournal username"] = "Nazwa użytkownika LiveJournal";
$a->strings["LiveJournal password"] = "Hasło LiveJournal";
$a->strings["Post to LiveJournal by default"] = "Opublikuj domyślnie w LiveJournal";
$a->strings["Submit"] = "Wyślij";
