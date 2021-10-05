<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Nitter server"] = "Nitter Server";
$a->strings["Save Settings"] = "Einstellungen Speichern";
$a->strings["In an attempt to protect your privacy, links to Twitter in this posting were replaced by links to the Nitter instance at %s"] = "Um deine Privatsphäre zu schützen, wurden in diesem Beitrag Links nach ";
