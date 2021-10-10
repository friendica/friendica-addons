<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Blockem'] = 'Blockem';
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Verbergt de inhoud van het bericht van de gebruiker. Daarnaast vervangt het de avatar door een standaardafbeelding.';
$a->strings['Comma separated profile URLS:'] = 'Profiel URLs (kommagescheiden):';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['BLOCKEM Settings saved.'] = 'BLOCKEM instellingen opgeslagen.';
$a->strings['Filtered user: %s'] = 'Gefilterde gebruiker: %s';
$a->strings['Unblock Author'] = 'Deblokkeer Auteur';
$a->strings['Block Author'] = 'Auteur blokkeren';
$a->strings['blockem settings updated'] = 'blockem instellingen opgeslagen';
