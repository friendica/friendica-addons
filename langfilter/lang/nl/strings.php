<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Taalfilter";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "Deze addon probeert de taal van berichten automatisch te bepalen. Als de taal van het bericht niet overeenkomt met een taal die jij spreekt zal het bericht worden verborgen. ";
$a->strings["Use the language filter"] = "Gebruik de taalfilter";
$a->strings["Able to read"] = "Kan lezen";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Lijst van afkortingen (ISO2 codes) voor talen die jij spreekt, door komma's gescheiden. Bijvoorbeeld \"de,it\".";
$a->strings["Minimum confidence in language detection"] = "Minimum betrouwbaarheid in taaldetectie";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Minimum betrouwbaarheid dat de correcte taal word gedetecteerd, van 0 tot 100. Berichten zullen niet worden gefilterd als de betrouwbaarheid lager is dan dit percentage.";
$a->strings["Minimum length of message body"] = "Minimum lengte van de berichttekst";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Minimum aantal letters en leestekens in de berichttekst nodig voor het filter om te werken. Kortere berichten worden niet gefilterd. NB: Taaldetectie is onbetrouwbaar voor korte berichten (<200 letters en leestekens).";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Language Filter Settings saved."] = "Taalfilter instellingen opgeslagen";
$a->strings["Filtered language: %s"] = "Gefilterde taal: %s";
