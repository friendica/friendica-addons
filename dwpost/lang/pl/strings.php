<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Post to Dreamwidth"] = "Opublikuj w Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Ustawienia postów w Dreamwerze";
$a->strings["Enable dreamwidth Post Addon"] = "Włącz dodatek dreamwidth Post";
$a->strings["dreamwidth username"] = "dreamwidth nazwa użytkownika";
$a->strings["dreamwidth password"] = "hasło dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Opublikuj domyślnie w serwisie dreamwidth";
$a->strings["Submit"] = "Wyślij";
