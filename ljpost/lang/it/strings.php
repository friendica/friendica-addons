<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to LiveJournal"] = "Invia a LiveJournal";
$a->strings["LiveJournal Post Settings"] = "Impostazioni invio a LiveJournal";
$a->strings["Enable LiveJournal Post Addon"] = "Abilita il componente aggiuntivo di invio a LiveJournal";
$a->strings["LiveJournal username"] = "Nome utente LiveJournal";
$a->strings["LiveJournal password"] = "Password LiveJournal";
$a->strings["Post to LiveJournal by default"] = "Invia sempre a LiveJournal";
$a->strings["Submit"] = "Invia";
