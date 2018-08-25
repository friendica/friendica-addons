<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Redirect URL"] = "URL doorverwijzing";
$a->strings["all your visitors from the web will be redirected to this URL"] = "al je bezoekers van het internet zullen worden doorverwezen naar deze URL";
$a->strings["Begin of the Blackout"] = "Begin van de Blackout";
$a->strings["format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute"] = "formaat is <em>JJJJ</em> jaar, <em>MM</em> maand, <em>DD</em> dag, <em>uu</em> uur en <em>mm</em> minuten";
$a->strings["End of the Blackout"] = "Einde van de Blackout";
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this."] = "De einddatum van de blackout is eerder dan de startdatum, verbeter dit.";
