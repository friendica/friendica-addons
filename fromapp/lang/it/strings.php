<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Fromapp settings updated."] = "Impostazioni \"FromApp\" aggiornato.";
$a->strings["FromApp Settings"] = "Imnpostazioni \"FromApp\"";
$a->strings["The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting."] = "Il nome applicazione che vuoi compaia come origine dei tuoi messaggi. Separa differenti nomi con una virgola. Di questi, un nome a caso verrà selezionato per ogni invio.";
$a->strings["Use this application name even if another application was used."] = "Usa questo nome anche se un'altra applicazione è stata effettivamente usata.";
$a->strings["Save Settings"] = "Salva Impostazioni";
