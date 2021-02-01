<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Markdown"] = "Markdown";
$a->strings["Enable Markdown parsing"] = "Abilita analisi Markdown";
$a->strings["If enabled, self created items will additionally be parsed via Markdown."] = "Se abilitato, gli elementi creati saranno analizzati via Markdown.";
$a->strings["Save Settings"] = "Salva Impostazioni";
