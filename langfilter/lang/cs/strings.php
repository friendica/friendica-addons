<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Language Filter"] = "Jazykový filtr";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "Tento doplněk zkouší identifikovat jazyk, ve kterém jsou napsány příspěvky. Pokud nelze přiřadit příspěvky k žádnému níže specifikovanému jazyku, příspěvky budou zabaleny.";
$a->strings["Use the language filter"] = "Použít jazykový filtr";
$a->strings["Able to read"] = "Lze číst";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Seznam zkratek (kódů ISO 2) pro jazyky, kterými mluvíte, oddělených čárkami. Příklad: \"cs,sk\".";
$a->strings["Minimum confidence in language detection"] = "Minimální jistota v detekci jazyka";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Minimální jistota ve správnost detekce jazyka, od 0 do 100. Pokud jistota v detekci spadne pod tuto úroveň, nebudou příspěvky filtrovány.";
$a->strings["Minimum length of message body"] = "Minimální délka těla zprávy";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Minimální počet znaků v těle zprávy pro použití filtru. Kratší zprávy nebudou filtrovány. Poznámka: Detekce jazyka je nespolehlivá pro krátký obsah (<200 znaků).";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Language Filter Settings saved."] = "Nastavení Language Filter uložena.";
$a->strings["Filtered language: %s"] = "Filtrovaný jazyk: %s";
