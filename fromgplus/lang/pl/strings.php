<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Google+ Mirror"] = "Lustro Google+";
$a->strings["Enable Google+ Import"] = "Włącz importowanie Google+";
$a->strings["Google Account ID"] = "Identyfikator konta Google";
$a->strings["Add keywords to post"] = "Dodaj słowa kluczowe do opublikowania";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Google+ Import Settings saved."] = "Zapisano ustawienia importu Google+.";
$a->strings["Key"] = "Klucz";
$a->strings["Settings updated."] = "Ustawienia zaktualizowane.";
