<?php

if(! function_exists("string_plural_select_et")) {
function string_plural_select_et($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Keelefilter";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "See lisa üritab määrata keelt milles postitused on kirjutatud. Kui tule, ei sobi ühegi alloleva keelega, siis postitused peidetakse minimeerides nad. ";
$a->strings["Use the language filter"] = "Kasuta keelefiltrit";
$a->strings["Able to read"] = "Suuteline lugema";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Nimistu koodidest (iso2 koodid) keelte kohta mida räägite, komaga eraldatult. Näiteks \"de, it, et, fi\". ";
$a->strings["Minimum confidence in language detection"] = "Miinimumkindlus keeletuvastusel";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Miinimumkindlus kontrollil 0-st 100-ni. Postitusi ei filtreerita kui tuvastuskindlus on allpool nimetatud protsendiväärtust. ";
$a->strings["Minimum length of message body"] = "Sõnumiteksti miinimumväärtus";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Miinimumväärtus märkides sõnumitekstis filtri tarbeks. Sellest lühemaid sõnumeid ei filtreerita. Märge: Keeletuvastus on ebausaldusväärne lühisisu puhul (vähem kui 200 märki). ";
$a->strings["Save Settings"] = "Salvesta sätted";
$a->strings["Language Filter Settings saved."] = "Keelefiltri sätted salvestatud.";
$a->strings["Filtered language: %s"] = "Filtreeritud keel: %s ";
