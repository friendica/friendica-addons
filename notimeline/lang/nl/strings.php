<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["No Timeline settings updated."] = "Geen tijdlijn instellingen opgeslagen";
$a->strings["No Timeline Settings"] = "No Timeline instellingen";
