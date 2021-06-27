<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Client ID"] = "Ügyfél-azonosító";
$a->strings["Client secret"] = "Ügyféltitok";
$a->strings["OpenID Connect endpoint"] = "OpenID Connect végpont";
$a->strings["Save Settings"] = "Beállítások mentése";
