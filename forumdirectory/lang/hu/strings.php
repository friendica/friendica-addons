<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Forum Directory"] = "Fórumkönyvtár";
$a->strings["Public access denied."] = "Nyilvános hozzáférés megtagadva.";
$a->strings["Global Directory"] = "Globális könyvtár";
$a->strings["Find on this site"] = "Keresés ezen az oldalon";
$a->strings["Finding: "] = "Találat: ";
$a->strings["Site Directory"] = "Oldal könyvtára";
$a->strings["Find"] = "Keresés";
$a->strings["Age: "] = "Életkor: ";
$a->strings["Gender: "] = "Nem: ";
$a->strings["Location:"] = "Hely:";
$a->strings["Gender:"] = "Nem:";
$a->strings["Status:"] = "Állapot:";
$a->strings["Homepage:"] = "Honlap:";
$a->strings["About:"] = "Névjegy:";
$a->strings["No entries (some entries may be hidden)."] = "Nincsenek bejegyzések (néhány bejegyzés rejtve lehet).";
