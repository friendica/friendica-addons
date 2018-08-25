<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Administrator"] = "Beheerder";
$a->strings["Your account on %s will expire in a few days."] = "Uw account op %s zal over enkele dagen vervallen";
$a->strings["Your Friendica account is about to expire."] = "";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "";
