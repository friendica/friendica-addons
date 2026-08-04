<?php

if(! function_exists("string_plural_select_da_DK")) {
function string_plural_select_da_DK($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Denne tilføjelse søger efter specificerede ord/tekst i opslag og kollapser dem. Den kan bruges til at filtrere indhold tagget med for eksempel #NSFW, som kan være betragtet som uanstændigt på bestemte tidspunkter eller bestemte steder, som eksempelvis på arbejde. Den er også brugbar til at skjule irrelevant eller irriterende indhold fra at blive vist direkte.';
$a->strings['Enable Content filter'] = 'Aktivér indholdsfilter';
$a->strings['Comma separated list of keywords to hide'] = 'Kommasepareret liste af nøgleord som skal skjules';
$a->strings['Use /expression/ to provide regular expressions, #tag to specfically match hashtags (case-insensitive), or regular words (case-sensitive)'] = 'Brug /expression/ for at angive regulære udtryk, #tag for at matche specifikke hashtags (skelner ikke imellem store og små bogstaver) eller almindelige ord (skelner imellem store og små bogstaver)';
$a->strings['Content Filter (NSFW and more)'] = 'Indholdsfilter (NSFW og mere)';
$a->strings['Regular expression "%s" fails to compile'] = 'Regulært udtryk "%s" kan ikke anvendes';
$a->strings['Filtered tag: %s'] = 'Filtreret tag: %s';
$a->strings['Filtered word: %s'] = 'Filtreret ord: %s';
