<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Submit"] = "Opslaan";
$a->strings["Tile Server URL"] = "URL met kaarttegels";
$a->strings["Settings updated."] = "Instellingen opgeslagen";
