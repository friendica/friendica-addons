<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"pageheader" Settings'] = 'Oldalfejléc beállításai';
$a->strings['Message'] = 'Üzenet';
$a->strings['Message to display on every page on this server (or put a pageheader.html file in your docroot)'] = 'A kiszolgálón lévő összes oldalon megjelenítendő üzenet (vagy tegyen egy pageheader.html fájlt a dokumentumgyökérbe)';
$a->strings['Save Settings'] = 'Beállítások mentése';
