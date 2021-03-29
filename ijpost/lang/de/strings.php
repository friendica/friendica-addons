<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Insanejournal"] = "Auf InsaneJournal posten.";
$a->strings["InsaneJournal Export"] = "InsaneJournal Export";
$a->strings["Enable InsaneJournal Post Addon"] = "InsaneJournal-Addon aktivieren";
$a->strings["InsaneJournal username"] = "InsaneJournal-Benutzername";
$a->strings["InsaneJournal password"] = "InsaneJournal-Passwort";
$a->strings["Post to InsaneJournal by default"] = "Standardmäßig auf InsaneJournal posten.";
$a->strings["Save Settings"] = "Einstellungen speichern";
