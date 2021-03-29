<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Forum Directory"] = "Elenco Forum";
$a->strings["Public access denied."] = "Accesso negato.";
$a->strings["No entries (some entries may be hidden)."] = "Nessuna voce (qualche voce potrebbe essere nascosta).";
$a->strings["Global Directory"] = "Elenco globale";
$a->strings["Find on this site"] = "Cerca nel sito";
$a->strings["Results for:"] = "Risultati per:";
$a->strings["Find"] = "Trova";
