<?php

if(! function_exists("string_plural_select_en_US")) {
function string_plural_select_en_US($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Diaspora'] = 'Post to Diaspora';
$a->strings['Enable Diaspora Post Addon'] = 'Enable Diaspora export';
$a->strings['Diaspora password'] = 'Diaspora password';
$a->strings['Post to Diaspora by default'] = 'Post to Diaspora by default';
$a->strings['Diaspora Export'] = 'Diaspora Export';
