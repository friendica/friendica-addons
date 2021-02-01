<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Markdown"] = "Markdown";
$a->strings["Enable Markdown parsing"] = "Verwende Markdown Formatierung";
$a->strings["Save Settings"] = "Einstellungen speichern";
