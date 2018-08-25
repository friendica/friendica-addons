<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Impressum"] = "Colofon";
$a->strings["Site Owner"] = "Siteeigenaar";
$a->strings["Email Address"] = "";
$a->strings["Postal Address"] = "";
$a->strings["The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon."] = "";
$a->strings["Settings updated."] = "Instellingen opgeslagen";
$a->strings["Submit"] = "";
$a->strings["The page operators name."] = "";
$a->strings["Site Owners Profile"] = "";
$a->strings["Profile address of the operator."] = "";
$a->strings["How to contact the operator via snail mail. You can use BBCode here."] = "";
$a->strings["Notes"] = "";
$a->strings["Additional notes that are displayed beneath the contact information. You can use BBCode here."] = "";
$a->strings["How to contact the operator via email. (will be displayed obfuscated)"] = "";
$a->strings["Footer note"] = "";
$a->strings["Text for the footer. You can use BBCode here."] = "";
