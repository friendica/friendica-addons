<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Language Filter"] = "Filtro Lingua";
$a->strings["This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them."] = "Questo componente aggiuntivo prova ad identificare la lingua usata in un messaggio. Se questa non corrisponde a una delle lingue specificata qui sotto, il messaggio verrà collassato.";
$a->strings["Use the language filter"] = "Usa il filtro lingua";
$a->strings["Able to read"] = "In grado di leggere";
$a->strings["List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Lista di abbreviazioni (codici ISO 639-1) per le lingue che parli, separate da virgola. Per esempio \"it,de\".";
$a->strings["Minimum confidence in language detection"] = "Fiducia minima nel rilevamento della lingua";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Fiducia minima che il rilevamento della lingua sia corretto, da 0 a 100. I messaggi non saranno filtrati quando la fiducia nel rilevamento della lingua è sotto questo valore percentuale.";
$a->strings["Minimum length of message body"] = "Lunghezza minima del corpo del messaggio";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Numero di caratteri minimo perché il filtro venga usato. I messaggio più corti non saranno filtrati. Nota: la rilevazione della lingua non è affidabile con messaggi brevi (<200 caratteri)";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Filtered language: %s"] = "Lingua filtrata: %s";
