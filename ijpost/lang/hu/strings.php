<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Insanejournal"] = "Beküldés az InsaneJournalra";
$a->strings["InsaneJournal Export"] = "InsaneJournal exportálás";
$a->strings["Enable InsaneJournal Post Addon"] = "Az InsaneJournal beküldési bővítmény engedélyezése";
$a->strings["InsaneJournal username"] = "InsaneJournal felhasználónév";
$a->strings["InsaneJournal password"] = "InsaneJournal jelszó";
$a->strings["Post to InsaneJournal by default"] = "Beküldés az InsaneJournalra alapértelmezetten";
$a->strings["Save Settings"] = "Beállítások mentése";
