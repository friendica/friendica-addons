<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Nitter server"] = "Nitter-kiszolgáló";
$a->strings["Save Settings"] = "Beállítások mentése";
