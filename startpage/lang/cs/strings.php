<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Startpage Settings"] = "Nastavení startovní stránky";
$a->strings["Home page to load after login  - leave blank for profile wall"] = "Domovská stránka k načtení po přihlášení  - pro profilovou zeď ponechejte prázdné";
$a->strings["Examples: &quot;network&quot; or &quot;notifications/system&quot;"] = "Příklady: &quot;network&quot; nebo &quot;notifications/system&quot;";
$a->strings["Submit"] = "Odeslat";
