<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"pageheader" Settings'] = 'Pageheader settings';
$a->strings['Message'] = 'Message';
$a->strings['Message to display on every page on this server (or put a pageheader.html file in your docroot)'] = 'Message to display above every page on this server (alternatively, put a pageheader.html file in your docroot)';
$a->strings['Save Settings'] = 'Save settings';
$a->strings['pageheader Settings saved.'] = 'Pageheader settings saved.';
