<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Upload a file"] = "Upload een bestand";
$a->strings["Drop files here to upload"] = "Sleep uw bestanden hier om ze te uploaden";
