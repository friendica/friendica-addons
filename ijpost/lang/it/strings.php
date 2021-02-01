<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Insanejournal"] = "Invia a InsaneJournal";
$a->strings["InsaneJournal Post Settings"] = "Impostazioni post InsaneJournal";
$a->strings["Enable InsaneJournal Post Addon"] = "Abilita il componente aggiuntivo di invio a InsaneJournal";
$a->strings["InsaneJournal username"] = "Nome utente InsaneJournal";
$a->strings["InsaneJournal password"] = "Password InsaneJournal";
$a->strings["Post to InsaneJournal by default"] = "Invia sempre a InsaneJournal";
$a->strings["Submit"] = "Invia";
