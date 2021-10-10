<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to libertree'] = 'Plaatsen op Libertree';
$a->strings['libertree Post Settings'] = 'Libertree Post instellingen';
$a->strings['Enable Libertree Post Addon'] = 'Libertree Post Addon inschakelen';
$a->strings['Post to Libertree by default'] = 'Plaatsen op Libertree als standaard instellen ';
