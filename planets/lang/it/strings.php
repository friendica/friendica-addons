<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Planets'] = 'Pianeti';
$a->strings['Planets Settings'] = 'Impostazioni "Pianeti"';
$a->strings['Enable Planets Addon'] = 'Abilita il componente aggiuntivo Pianeti';
$a->strings['Save Settings'] = 'Salva Impostazioni';
