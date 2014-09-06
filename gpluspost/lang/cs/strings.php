<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Post to Google+"] = "Příspěvek na Google+";
$a->strings["Enable Google+ Post Plugin"] = "Povolit Google+ Plugin";
$a->strings["Google+ username"] = "Google+ uživatelské jméno";
$a->strings["Google+ password"] = "Google+ heslo";
$a->strings["Google+ page number"] = "Google+ číslo stránky";
$a->strings["Post to Google+ by default"] = "Defaultně zaslat na Google+";
$a->strings["Do not prevent posting loops"] = "Nezabraňovat cyklení příspěvků ";
$a->strings["Skip messages without links"] = "Přeskakovat zprávy bez odkazů";
$a->strings["Mirror all public posts"] = "Zrcadlit všechny veřejné příspěvky";
$a->strings["Mirror Google Account ID"] = "ID účtu Google pro zrcadlení";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Google+ post failed. Queued for retry."] = "Zaslání příspěvku na Google+ selhalo. Příspěvek byl zařazen do fronty pro opakované odeslání.";
