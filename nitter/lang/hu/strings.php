<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Nitter server"] = "Nitter-kiszolgáló";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["In an attempt to protect your privacy, links to Twitter in this posting were replaced by links to the Nitter instance at %s"] = "A magánélet védelme érdekében az ebben a bejegyzésben lévő Twitterre mutató ";
