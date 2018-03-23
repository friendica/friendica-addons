<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Post to LiveJournal"] = "Poslat na LiveJournal";
$a->strings["LiveJournal Post Settings"] = "Nastavení LiveJournal Post";
$a->strings["Enable LiveJournal Post Addon"] = "Povolit LiveJournal Post addon";
$a->strings["LiveJournal username"] = "LiveJournal uživatelské jméno";
$a->strings["LiveJournal password"] = "LiveJournal heslo";
$a->strings["Post to LiveJournal by default"] = "Defaultně umístit na LiveJournal";
$a->strings["Submit"] = "Odeslat";
