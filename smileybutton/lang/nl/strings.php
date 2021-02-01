<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Smileybutton settings"] = "Smileybutton instellingen";
$a->strings["Save Settings"] = "Instellingen opslaan";
