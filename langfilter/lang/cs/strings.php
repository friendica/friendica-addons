<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Language Filter"] = "Jazykový filtr";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "";
$a->strings["Use the language filter"] = "Použít jazykový filtr";
$a->strings["Able to read"] = "";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "";
$a->strings["Minimum confidence in language detection"] = "";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "";
$a->strings["Minimum length of message body"] = "";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Language Filter Settings saved."] = "";
$a->strings["Filtered language: %s"] = "Filtrovaný jazyk: %s";
