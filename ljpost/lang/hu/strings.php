<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to LiveJournal"] = "Beküldés a LiveJournalra";
$a->strings["LiveJournal Post Settings"] = "LiveJournal-beküldés beállításai";
$a->strings["Enable LiveJournal Post Addon"] = "A LiveJournal-beküldő bővítmény engedélyezése";
$a->strings["LiveJournal username"] = "LiveJournal felhasználónév";
$a->strings["LiveJournal password"] = "LiveJournal jelszó";
$a->strings["Post to LiveJournal by default"] = "Beküldés a LiveJournalra alapértelmezetten";
$a->strings["Save Settings"] = "Beállítások mentése";
