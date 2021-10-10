<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Planets'] = 'Planeten';
$a->strings['Planets Settings'] = 'Planeten-Einstellungen';
$a->strings['Enable Planets Addon'] = 'Planeten-Addon aktivieren';
$a->strings['Save Settings'] = 'Einstellungen speichern';
