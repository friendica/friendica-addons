<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Allow \"good\" crawlers"] = "Permetti crawler \"buoni\"";
$a->strings["Block GabSocial"] = "Blocca GabSocial";
$a->strings["Training mode"] = "Modalità addestramento";
$a->strings["Settings updated."] = "Impostazioni aggiornate.";
