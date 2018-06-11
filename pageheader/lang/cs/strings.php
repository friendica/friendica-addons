<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["\"pageheader\" Settings"] = "Nastavení záhlaví stránky";
$a->strings["Message"] = "Zpráva";
$a->strings["Message to display on every page on this server (or put a pageheader.html file in your docroot)"] = "Zpráva, která má být zobrazena na každé stránce tohoto serveru (nebo vložte soubor pageheader.html do kořenové složky Vašeho serveru)";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["pageheader Settings saved."] = "Nastavení záhlaví stránky uloženo.";
