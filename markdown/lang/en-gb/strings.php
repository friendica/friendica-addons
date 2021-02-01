<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Markdown"] = "Markdown";
$a->strings["Enable Markdown parsing"] = "Enable Markdown parsing";
$a->strings["Save Settings"] = "Save Settings";
