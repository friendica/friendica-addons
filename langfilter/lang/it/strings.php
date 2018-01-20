<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Filtro Lingua";
$a->strings["This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings."] = "Questo addon prova ad identificare la lingua usata in un messaggio. Se questa non corrisponde a una delle lingue da te parlata (vedi sotto), il messaggio verrà nascosto. Ricorda che la rilevazione della lingua non è perfetta, specie con i messaggi corti.";
$a->strings["Use the language filter"] = "Usa il filtro lingua";
$a->strings["I speak"] = "Parlo";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Lista di abbreviazioni (codici iso2) per le lingue che parli, separate da virgola. Per esempio \"it,de\"";
$a->strings["Minimum confidence in language detection"] = "Fiducia minima nel rilevamento della lingua";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Fiducia minima che il rilevamento della lingua sia corretto, da 0 a 100. I post non saranno filtrati quando la fiducia nel rilevamento della lingua è sotto questo valore percentuale.";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Language Filter Settings saved."] = "Impostazioni Filtro Lingua salvate.";
$a->strings["unspoken language %s - Click to open/close"] = "lingua non parlata %s - Clicca per aprire/chiudere";
