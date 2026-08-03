<?php

if(! function_exists("string_plural_select_en_US")) {
function string_plural_select_en_US($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Redirect URL'] = 'Redirect URL';
$a->strings['Begin of the Blackout'] = 'Start time of the Blackout';
$a->strings['End of the Blackout'] = 'End time of the Blackout';
