<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["From Address"] = "Lähettäjä";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Re:"] = "Koskien";
$a->strings["Friendica post"] = "Friendica -julkaisu";
$a->strings["Diaspora post"] = "Diaspora -julkaisu";
$a->strings["Email"] = "Sähköposti";
$a->strings["Friendica Item"] = "Friendica -kohde";
$a->strings["Local"] = "Paikallinen";
$a->strings["Enabled"] = "Käytössä";
$a->strings["Email Address"] = "Sähköpostiosoite";
$a->strings["Attach Images"] = "Liitä kuvia";
$a->strings["Mail Stream Settings"] = "Mail Stream -asetukset";
