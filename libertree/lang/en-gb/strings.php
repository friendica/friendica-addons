<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to libertree'] = 'Post to Libertree';
$a->strings['Enable Libertree Post Addon'] = 'Enable Libertree post addon';
$a->strings['Libertree site URL'] = 'Libertree site URL';
$a->strings['Libertree API token'] = 'Libertree API token';
$a->strings['Post to Libertree by default'] = 'Post to Libertree by default';
