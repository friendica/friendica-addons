<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Post to blogger"] = "Poslat na blogger";
$a->strings["Blogger Export"] = "Blogger Export";
$a->strings["Enable Blogger Post Addon"] = "Povolit doplněk Blogger Post";
$a->strings["Blogger username"] = "Blogger uživatelské jméno";
$a->strings["Blogger password"] = "Blogger heslo";
$a->strings["Blogger API URL"] = "Blogger API URL";
$a->strings["Post to Blogger by default"] = "Defaultně zaslat na Blogger";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Post from Friendica"] = "Příspěvek z Friendica";
