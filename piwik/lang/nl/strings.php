<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This website is tracked using the <a href=\'http://www.piwik.org\'>Piwik</a> analytics tool.'] = 'Deze website word gevolgd door <a href=\'http://www.piwik.org\'>Piwik</a> analytics';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['Settings updated.'] = 'Instellingen opgeslagen';
