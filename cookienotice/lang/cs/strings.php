<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["\"cookienotice\" Settings"] = "Nastavení „cokienotice“";
$a->strings["<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button."] = "";
$a->strings["Cookie Usage Notice"] = "";
$a->strings["The cookie usage notice"] = "";
$a->strings["OK Button Text"] = "Text tlačítka OK";
$a->strings["The OK Button text"] = "Text tlačítka OK";
$a->strings["Save Settings"] = "";
$a->strings["cookienotice Settings saved."] = "Nastavení cookienotice uložena. ";
