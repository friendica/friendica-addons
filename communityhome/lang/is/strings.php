<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return ($n % 10 != 1 || $n % 100 == 11);;
}}
;
$a->strings["Login"] = "Innskráning";
$a->strings["OpenID"] = "";
$a->strings["Latest users"] = "";
$a->strings["Most active users"] = "";
$a->strings["Latest photos"] = "";
$a->strings["Contact Photos"] = "";
$a->strings["Profile Photos"] = "";
$a->strings["Latest likes"] = "";
$a->strings["event"] = "";
$a->strings["status"] = "";
$a->strings["photo"] = "";
$a->strings["%1\$s likes %2\$s's %3\$s"] = "";
$a->strings["Welcome to %s"] = "";
