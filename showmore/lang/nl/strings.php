<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"Show more" Settings'] = '"Toon meer" instellingen';
$a->strings['Enable Show More'] = 'Toon meer inschakelen';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['Show More Settings saved.'] = 'Toon meer instellingen opgeslagen.';
