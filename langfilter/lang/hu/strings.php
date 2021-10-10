<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Language Filter'] = 'Nyelvszűrő';
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Ez a bővítmény megpróbálja azonosítani, hogy a bejegyzéseket milyen nyelven írták. Ha nem egyezik egyetlen lent megadott nyelvvel sem, akkor a bejegyzések rejtettek lesznek azáltal, hogy össze lesznek csukva.';
$a->strings['Use the language filter'] = 'A nyelvszűrő használata';
$a->strings['Able to read'] = 'Képes olvasni';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Az Ön által beszélt nyelvek rövidítéseinek listája (ISO 639-1 kódok) vesszővel elválasztva. Például „de,it”.';
$a->strings['Minimum confidence in language detection'] = 'Legkisebb megbízhatóság a nyelvfelismerésben';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'A legkisebb megbízhatóság a helyesnek tűnő nyelvfelismerésben 0-tól 100-ig. A bejegyzések nem lesznek szűrve, ha a nyelvfelismerés megbízhatósága ezen százalékérték alatt van.';
$a->strings['Minimum length of message body'] = 'Üzenettörzs legkisebb hossza';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Az üzenet törzsében lévő karakterek legkisebb száma a használandó szűrőnél. Az ennél rövidebb bejegyzések nem lesznek szűrve. Megjegyzés: a nyelvfelismerés megbízhatatlan a rövid tartalmaknál (200-nál kevesebb karakternél).';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Filtered language: %s'] = 'Szűrt nyelv: %s';
