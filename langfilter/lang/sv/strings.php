<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Detta tillägg försöker identifiera vilket språk inlägg är skrivna i. Om det inte matchar ett språk specificerat nedan så göms inlägget genom att kollapsa det.';
$a->strings['Use the language filter'] = 'Använd språkfiltret';
$a->strings['Able to read'] = 'Kan läsa';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Lista över förkortningar (ISO 639-1-koder) för språk du använder, separerade med kommatecken. Exempel: "de, it".';
$a->strings['Minimum confidence in language detection'] = 'Minsta förtroende i språkigenkänning';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Minsta förtroende i att språkigenkänningen är korrekt, från 0 till 100.
Inlägg filtreras inte när förtroendet i språkigenkänningen är under detta procentvärde.';
$a->strings['Minimum length of message body'] = 'Minsta längd på meddelandetext';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Minsta antal tecken i meddelandetext för att ett filter ska användas. Inlägg kortare än detta kommer inte att filtreras. Notera: Språkigenkänning är inte tillförlitligt på korta texter (<200 tecken).';
$a->strings['Language Filter'] = 'Språkfilter';
$a->strings['Save Settings'] = 'Spara inställningar';
$a->strings['Filtered language: %s'] = 'Filtrerat språk: %s';
