<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Wordpress"] = "Plaatsen op Wordpress";
$a->strings["Wordpress Export"] = "Wordpress Exporteren";
$a->strings["Enable WordPress Post Addon"] = "Wordpress Post Addon Inschakelen";
$a->strings["Post to WordPress by default"] = "Plaatsen op Wordpress als standaard instellen ";
$a->strings["Provide a backlink to the Friendica post"] = "Geef een terugkoppeling naar get Friendica bericht";
$a->strings["Don't post messages that are too short"] = "Plaats geen berichten die te kort zijn";
$a->strings["Save Settings"] = "Instellingen opslaan";
