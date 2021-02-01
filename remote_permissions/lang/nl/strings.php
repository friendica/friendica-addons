<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Remote Permissions Settings"] = "Instellingen voor toegang op afstand";
$a->strings["Allow recipients of your private posts to see the other recipients of the posts"] = "Ontvangers van private berichten toestaan andere ontvangers te bekijken";
$a->strings["Remote Permissions settings updated."] = "Toegang op afstand instellingen opgeslagen";
$a->strings["Settings updated."] = "Instellingen opgeslagen";
