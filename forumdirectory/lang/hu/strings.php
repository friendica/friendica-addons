<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Forum Directory"] = "Fórumkönyvtár";
$a->strings["Public access denied."] = "Nyilvános hozzáférés megtagadva.";
$a->strings["No entries (some entries may be hidden)."] = "Nincsenek bejegyzések (néhány bejegyzés rejtve lehet).";
$a->strings["Global Directory"] = "Globális könyvtár";
$a->strings["Find on this site"] = "Keresés ezen az oldalon";
$a->strings["Results for:"] = "Találatok ehhez:";
$a->strings["Find"] = "Keresés";
