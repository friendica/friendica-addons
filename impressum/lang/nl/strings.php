<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Impressum"] = "Colofon";
$a->strings["Site Owner"] = "Siteeigenaar";
$a->strings["Settings updated."] = "Instellingen opgeslagen";
