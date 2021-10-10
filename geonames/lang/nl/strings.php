<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Geonames settings updated.'] = 'Geonames instellingen opgeslagen';
$a->strings['Geonames Settings'] = 'Geonames Instellingen';
$a->strings['Enable Geonames Addon'] = 'Geonames Addon inschakelen';
$a->strings['Submit'] = 'Toepassen';
