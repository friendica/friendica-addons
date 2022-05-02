<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Impressum'] = 'Indtryk';
$a->strings['Site Owner'] = 'Sideejer';
$a->strings['Email Address'] = 'Email-adresse';
$a->strings['Postal Address'] = 'Postadresse';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'Indtryk-tilføjelsen skal konfigureres!<br />Tilføj venligst som det allermindste <tt>owner</tt> (sideejer) variablen til din konfigurationsfil. For andre variabler, referer venligst til tilføjelsens README fil.';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['The page operators name.'] = 'Sideoperatorens navn.';
$a->strings['Site Owners Profile'] = 'Sideejerens profil';
$a->strings['Profile address of the operator.'] = 'Operatorens profiladresse.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'Hvordan man kan kontakte operatoren via sneglepost. Du kan bruge BBCode her.';
$a->strings['Notes'] = 'Noter';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Ekstra noter som er vist under kontaktinformationen. Du kan bruge BBCode her.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'Hvordan man kan kontakte operatoren via email. (vil blive vist obfuskeret)';
$a->strings['Footer note'] = 'Sidefodsnote';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Tekst til sidefoden. Du kan bruge BBCode her.';
