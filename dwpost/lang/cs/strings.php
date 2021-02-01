<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Post to Dreamwidth"] = "Poslat na Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Nastavení Dreamwidth Post";
$a->strings["Enable dreamwidth Post Addon"] = "Povolit doplněk Dreamwidth Post";
$a->strings["dreamwidth username"] = "dreamwidth uživatelské jméno";
$a->strings["dreamwidth password"] = "dreamwidth heslo";
$a->strings["Post to dreamwidth by default"] = "Ve výchozím stavu posílat na dreamwidth";
$a->strings["Submit"] = "Odeslat";
