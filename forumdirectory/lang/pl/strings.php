<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Forum Directory"] = "Katalog forum";
$a->strings["Public access denied."] = "Odmowa dostępu publicznego.";
$a->strings["Global Directory"] = "Globalny katalog";
$a->strings["Find on this site"] = "Znajdź na tej stronie";
$a->strings["Finding: "] = "Odkrycie:";
$a->strings["Site Directory"] = "Katalog Strony";
$a->strings["Find"] = "Szukaj";
$a->strings["Age: "] = "Wiek:";
$a->strings["Gender: "] = "Płeć:";
$a->strings["Location:"] = "Lokalizacja";
$a->strings["Gender:"] = "Płeć:";
$a->strings["Status:"] = "Status";
$a->strings["Homepage:"] = "Strona główna:";
$a->strings["About:"] = "O:";
$a->strings["No entries (some entries may be hidden)."] = "Brak wpisów (niektóre wpisy mogą być ukryte).";
