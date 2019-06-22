<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Sprachfilter";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "Dieses Addon versucht zu identifizieren, in welcher Sprache Posts geschrieben werden. Wenn sie nicht mit einer der unten angegebenen Sprachen übereinstimmt, werden Posts durch Zusammenklappen verborgen.";
$a->strings["Use the language filter"] = "Den Sprachfilter verwenden";
$a->strings["Able to read"] = "Lesbar";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Liste von Abkürzungen (ISO2-Codes) der Sprachen, die du sprichst, getrennt durch Komma. Zum Beispiel: \"de,it\".";
$a->strings["Minimum confidence in language detection"] = "Minimales Vertrauenslevel in die Spracherkennung";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Minimales Vertrauen in die Richtigkeit der erkannten Sprache. Wert zwischen 0 und 100. Beiträge mit einem niedrigeren Vertrauenslevel werden nicht gefiltert.";
$a->strings["Minimum length of message body"] = "Mindestlänge des Nachrichtentexts";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Mindestanzahl von Zeichen im Nachrichtentext, die vom Filter benutzt werden soll. Kürzere Beiträge werden nicht gefiltert. Hinweis: Die Spracherkennung ist bei kurzen Inhalten (<200 Zeichen) nicht zuverlässig.";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Language Filter Settings saved."] = "Sprachfilter-Einstellungen gespeichert.";
$a->strings["Filtered language: %s"] = "Gefilterte Sprache:  %s";
