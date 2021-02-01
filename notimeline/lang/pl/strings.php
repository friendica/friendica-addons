<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["No Timeline settings updated."] = "Nie zaktualizowano ustawień osi czasu.";
$a->strings["No Timeline Settings"] = "Brak ustawień osi czasu";
$a->strings["Disable Archive selector on profile wall"] = "Wyłącz selektor Archiwum na ścianie profilowej";
$a->strings["Submit"] = "Wyślij";
