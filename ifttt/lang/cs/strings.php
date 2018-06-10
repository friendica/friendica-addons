<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["IFTTT Mirror"] = "Zrcadlení IFTTT";
$a->strings["Create an account at <a href=\"http://www.ifttt.com\">IFTTT</a>. Create three Facebook recipes that are connected with <a href=\"https://ifttt.com/maker\">Maker</a> (In the form \"if Facebook then Maker\") with the following parameters:"] = "";
$a->strings["Body for \"new status message\""] = "";
$a->strings["Body for \"new photo upload\""] = "";
$a->strings["Body for \"new link post\""] = "";
$a->strings["Generate new key"] = "";
$a->strings["Save Settings"] = "Uložit nastavení";
