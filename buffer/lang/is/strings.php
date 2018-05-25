<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return ($n % 10 != 1 || $n % 100 == 11);;
}}
;
$a->strings["Permission denied."] = "Heimild ekki veitt.";
$a->strings["Save Settings"] = "";
$a->strings["Client ID"] = "";
$a->strings["Client Secret"] = "";
$a->strings["Error when registering buffer connection:"] = "";
$a->strings["You are now authenticated to buffer. "] = "";
$a->strings["return to the connector page"] = "";
$a->strings["Post to Buffer"] = "";
$a->strings["Buffer Export"] = "";
$a->strings["Authenticate your Buffer connection"] = "";
$a->strings["Enable Buffer Post Addon"] = "";
$a->strings["Post to Buffer by default"] = "";
$a->strings["Check to delete this preset"] = "";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "";
