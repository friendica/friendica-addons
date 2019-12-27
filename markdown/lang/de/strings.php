<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Markdown"] = "Markdown";
$a->strings["Enable Markdown parsing"] = "Verwende Markdown Formatierung";
$a->strings["If enabled, self created items will additionally be parsed via Markdown."] = "Wenn diese Option aktiviert ist, werden alle deine neu erstellten Beiträge beim Senden zusätzlich zum BBCode auch Markdown Formatierungen angewandt.";
$a->strings["Save Settings"] = "Einstellungen speichern";
