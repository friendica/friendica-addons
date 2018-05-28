<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["\"Blockem\""] = "\"Blockem\"";
$a->strings["Hides user's content by collapsing posts. Also replaces their avatar with generic image."] = "Nascondi il contenuto degli utenti collassando i messaggi. Sostituisce anche gli avatar con un'immagine generica.";
$a->strings["Comma separated profile URLS:"] = "URL profili separati da virgola:";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["BLOCKEM Settings saved."] = "Impostazioni BLOCKEM salvate.";
$a->strings["Filtered user: %s"] = "Utente filtrato: %s";
$a->strings["Unblock Author"] = "Sblocca autore";
$a->strings["Block Author"] = "Blocca autore";
$a->strings["blockem settings updated"] = "Impostazioni 'blockem' aggiornate.";
