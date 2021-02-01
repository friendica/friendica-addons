<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Fromapp settings updated."] = "FromApp settings updated.";
$a->strings["FromApp Settings"] = "FromApp";
$a->strings["The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting."] = "The application name you would like to show your posts originating from. Separate different app names with a comma and a random one will be selected for every post.";
$a->strings["Use this application name even if another application was used."] = "Use this application name even if another application was used.";
$a->strings["Save Settings"] = "Save settings";
