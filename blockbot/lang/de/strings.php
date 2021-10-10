<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Allow "good" crawlers'] = '"Gute" Crawler erlauben';
$a->strings['Block GabSocial'] = 'GabSocial Instanzen blockieren';
$a->strings['Training mode'] = 'Trainingsmodus';
$a->strings['Settings updated.'] = 'Einstellungen aktualisiert.';
