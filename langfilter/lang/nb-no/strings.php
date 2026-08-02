<?php

if(! function_exists("string_plural_select_nb_no")) {
function string_plural_select_nb_no($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Language Filter'] = 'Språkfilter';
$a->strings['This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them.'] = 'Dette tillegget forsøker å identifisere hvilket språk poster er skrevet i. Hvis det ikke samsvarer med noen av språkene som er spesifisert nedenfor vil postene bli skjult ved at de blir kollapset.';
$a->strings['Use the language filter'] = 'Bruk språkfilter';
$a->strings['Able to read'] = 'I stand til å lese';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Liste over forkortelser (ISO 639-1 koder) for språk du snakker, kommaseparert. For eksempel "de,it"';
$a->strings['Minimum confidence in language detection'] = 'Minimum sikkerhet i språkdeteksjon.';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Minimum sikkerhet for å gi korrekt språkdeteksjon, fra 0 til 100. Poster vil ikke bli filtrert hvis sikkerheten i språkdeteksjonen er lavere enn denne prosentverdien.';
$a->strings['Minimum length of message body'] = 'MInimumslengde for meldingskropp.';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Minimum antall tegn i meldingskropp for å kunne benytte filter. Poster som er kortere enn dette vil ikke bli filtrert. Bemerk: Språkoppdagelse er upålitelig for kort innhold (< 200 tegn).';
$a->strings['Save Settings'] = 'Lagre innstillinger';
$a->strings['Filtered language: %s'] = 'Filtrert språk: 1 %s';
