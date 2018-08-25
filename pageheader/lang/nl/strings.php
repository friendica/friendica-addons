<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["\"pageheader\" Settings"] = "\"pageheader\" instellingen";
$a->strings["Message"] = "";
$a->strings["Message to display on every page on this server (or put a pageheader.html file in your docroot)"] = "";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["pageheader Settings saved."] = "Pageheader instellingen opgeslagen.";
