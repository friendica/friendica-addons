<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to blogger"] = "Lägg in på Blogger";
$a->strings["Blogger Export"] = "Export till Blogger";
$a->strings["Enable Blogger Post Addon"] = "Aktivera tillägg för Blogger-inlägg";
$a->strings["Blogger username"] = "Blogger användarnamn";
$a->strings["Blogger password"] = "Blogger lösenord";
$a->strings["Blogger API URL"] = "Blogger API URL";
$a->strings["Post to Blogger by default"] = "Lägg in på Blogger som standard";
$a->strings["Save Settings"] = "Spara inställningar";
$a->strings["Post from Friendica"] = "Inlägg från Friendica";
