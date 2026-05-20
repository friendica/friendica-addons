<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Geonames Addon'] = 'Enable Geonames Addon';
$a->strings['Geonames Settings'] = 'Geonames Settings';
