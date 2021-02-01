<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Superblock"] = "Superblock";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["SUPERBLOCK Settings saved."] = "SUPERBLOCK instellingen opgeslagen";
$a->strings["superblock settings updated"] = "Superblock instellingen opgeslagen";
