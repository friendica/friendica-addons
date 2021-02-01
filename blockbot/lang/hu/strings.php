<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Allow \"good\" crawlers"] = "A „jó” keresőrobotok engedélyezése";
$a->strings["Block GabSocial"] = "GabSocial tiltása";
$a->strings["Training mode"] = "Oktató mód";
$a->strings["Settings updated."] = "A beállítások frissítve.";
