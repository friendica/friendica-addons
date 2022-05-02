<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"pageheader" Settings'] = '"sidehoved" Indstillinger';
$a->strings['Message'] = 'Besked';
$a->strings['Message to display on every page on this server (or put a pageheader.html file in your docroot)'] = 'Besked at vise på hver side på denne server (eller put en pageheader.html fil i din docroot)';
$a->strings['Save Settings'] = 'Gem indstillinger';
