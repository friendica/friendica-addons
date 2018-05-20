<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Administrator"] = "Ylläpitäjä";
$a->strings["Your account on %s will expire in a few days."] = "%s -tilisi vanhenee muutaman päivän kuluttua.";
$a->strings["Your Friendica account is about to expire."] = "Friendica-tilisi umpeutuu kohta.";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "";
