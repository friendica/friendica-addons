<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["OK"] = "OK";
$a->strings["\"cookienotice\" Settings"] = "Nastavení „cookienotice“";
$a->strings["Cookie Usage Notice"] = "Oznámení o používání cookies";
$a->strings["The cookie usage notice"] = "Oznámení o používání cookies";
$a->strings["OK Button Text"] = "Text tlačítka OK";
$a->strings["The OK Button text"] = "Text tlačítka OK";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["cookienotice Settings saved."] = "Nastavení cookienotice uložena.";
