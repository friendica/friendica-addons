<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Superblock'] = 'Superblock';
$a->strings['Comma separated profile URLS to block'] = 'Profile URLs to block (separated by commas)';
$a->strings['Save Settings'] = 'Save settings';
$a->strings['SUPERBLOCK Settings saved.'] = 'Superblock settings saved.';
$a->strings['Block Completely'] = 'Block completely';
$a->strings['superblock settings updated'] = 'Superblock settings updated';
