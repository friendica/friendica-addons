<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Denne tilføjelse forsøger at identificere det sprog som opslag er skrevet i. Hvis opslaget ikke matcher en af sprogene specificeret herunder, bliver opslaget kollapset.';
$a->strings['Use the language filter'] = 'Brug sprogfilteret';
$a->strings['Able to read'] = 'Kan læse';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Liste af forkortelser (ISO 639-1 koder) for sprog som du taler, kommasepareret. For eksempel "da,en" for dansk og engelsk.';
$a->strings['Minimum confidence in language detection'] = 'Minimum konfidens i sprogregistreringen';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Minimum konfidens i at det registrerede sprog er korrekt, fra 0 til 100. Opslag bliver ikke filtreret når konfidensen af det registrerede sprog er under denne procentværdi.';
$a->strings['Minimum length of message body'] = 'Minimum længde af beskeden';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Minimum nummer af tegn i beskeden før filteret bruges. Opslag som er kortere end dette tal vil ikke blive filtreret. Note: Sprogregistreringen er upålidelig for kort indhold (<200 tegn).';
$a->strings['Language Filter'] = 'Sprogfilter';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Filtered language: %s'] = 'Filtreret sprog: %s';
