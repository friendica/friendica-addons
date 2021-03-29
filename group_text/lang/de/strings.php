<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Group Text"] = "Gruppen als Text";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Beim Bearbeiten von Gruppen Text statt Bilder anzeigen";
$a->strings["Save Settings"] = "Einstellungen speichern";
