<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Google+ Mirror"] = "Zrcadlení Google+";
$a->strings["Enable Google+ Import"] = "Povolit Import z Google+";
$a->strings["Google Account ID"] = "ID účtu Google ";
$a->strings["Add keywords to post"] = "Přidat k příspěvku klíčová slova";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Google+ Import Settings saved."] = "Nastavení importu z Google+ uloženo.";
$a->strings["Key"] = "Klíč";
$a->strings["Settings updated."] = "Nastavení aktualizována";
