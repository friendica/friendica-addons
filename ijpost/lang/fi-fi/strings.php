<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Insanejournal"] = "Lähetä InsaneJournaliin";
$a->strings["InsaneJournal Post Settings"] = "InsaneJournal -viestin asetukset";
$a->strings["Enable InsaneJournal Post Addon"] = "Ota InsaneJournal -viestilisäosa käyttöön";
$a->strings["InsaneJournal username"] = "InsaneJournal -käyttäjätunnus";
$a->strings["InsaneJournal password"] = "InsaneJournal -salasana";
$a->strings["Post to InsaneJournal by default"] = "Lähetä InsaneJournaliin oletuksena";
$a->strings["Submit"] = "Lähetä";
