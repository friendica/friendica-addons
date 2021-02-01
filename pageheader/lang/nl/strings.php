<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"pageheader\" Settings"] = "\"pageheader\" instellingen";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["pageheader Settings saved."] = "Pageheader instellingen opgeslagen.";
