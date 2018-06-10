<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Permission denied."] = "Přístup odmítnut.";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Client ID"] = "Client ID";
$a->strings["Client Secret"] = "Client Secret";
$a->strings["Error when registering buffer connection:"] = "Chyba při registraci buffer spojená";
$a->strings["You are now authenticated to buffer. "] = "Nyní jste přihlášen k bufferu.";
$a->strings["return to the connector page"] = "návrat ke stránce konektor";
$a->strings["Post to Buffer"] = "Příspěvek na Buffer";
$a->strings["Buffer Export"] = "Buffer Export";
$a->strings["Authenticate your Buffer connection"] = "Přihlásit ke spojení na Buffer";
$a->strings["Enable Buffer Post Addon"] = "Povolit doplněk Buffer Post";
$a->strings["Post to Buffer by default"] = "Defaultně zaslat na Buffer";
$a->strings["Check to delete this preset"] = "Zaškrtnout pro smazání tohoto nastavení";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Příspěvky jsou zasílány na všechny účty, které jsou defaultně povoleny:";
