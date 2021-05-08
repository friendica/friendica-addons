<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["generic profile image"] = "immagine generica del profilo";
$a->strings["random geometric pattern"] = "schema geometrico casuale";
$a->strings["monster face"] = "faccia di mostro";
$a->strings["computer generated face"] = "faccia generata dal computer";
$a->strings["retro arcade style face"] = "faccia stile retro arcade";
$a->strings["roboter face"] = "faccia robotica";
$a->strings["retro adventure game character"] = "personaggio di un gioco di avventura retrò";
$a->strings["Information"] = "Informazione";
$a->strings["Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar."] = "Il componente aggiuntivo Gravatar è installato. Disabilita il componente aggiuntivo Gravatar.<br> Il componente aggiuntivo Libravatar si appoggerà a Gravatar se non trova nulla su Libravatar.";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Default avatar image"] = "Immagine avatar predefinita";
$a->strings["Select default avatar image if none was found. See README"] = "Seleziona l'immagine di default se non viene  trovato niente. Vedi README";
