<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to blogger"] = "Post to Blogger";
$a->strings["Enable Blogger Post Addon"] = "Enable Blogger Post Addon";
$a->strings["Blogger username"] = "Blogger username";
$a->strings["Blogger password"] = "Blogger password";
$a->strings["Post to Blogger by default"] = "Post to Blogger by default";
$a->strings["Save Settings"] = "Save Settings";
$a->strings["Post from Friendica"] = "Post from Friendica";
