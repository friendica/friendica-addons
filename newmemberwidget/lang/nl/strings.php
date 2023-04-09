<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New Member'] = 'Nieuw lid';
$a->strings['Tips for New Members'] = 'Tips voor nieuwe leden';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['Message'] = 'Bericht';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Uw bericht aan nieuwe leden. U kunt hier bbcode gebruiken.';
