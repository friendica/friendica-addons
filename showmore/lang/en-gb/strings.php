<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Show More'] = 'Enable this addon?';
$a->strings['"Show more" Settings'] = 'Show More';
$a->strings['show more'] = 'show more';
