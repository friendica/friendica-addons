<?php

if(! function_exists("string_plural_select_en_us")) {
function string_plural_select_en_us($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Diaspora'] = 'Post to Diaspora';
$a->strings['Diaspora Export'] = 'Diaspora Export';
$a->strings['Save Settings'] = 'Save settings';
$a->strings['Enable Diaspora Post Addon'] = 'Enable Diaspora export';
$a->strings['Diaspora password'] = 'Diaspora password';
$a->strings['Post to Diaspora by default'] = 'Post to Diaspora by default';
