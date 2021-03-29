<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["IFTTT Mirror"] = "IFTTT tükör";
$a->strings["Body for \"new status message\""] = "Az „új állapotüzenet” törzse";
$a->strings["Body for \"new photo upload\""] = "Az „új fényképfeltöltés” törzse";
$a->strings["Body for \"new link post\""] = "Az „új hivatkozásbeküldés” törzse";
$a->strings["Generate new key"] = "Új kulcs előállítása";
$a->strings["Save Settings"] = "Beállítások mentése";
