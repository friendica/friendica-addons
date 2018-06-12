<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Permission denied."] = "Přístup odmítnut.";
$a->strings["Unable to register the client at the pump.io server '%s'."] = "Nebylo možné registrovat klienta na pump.io serveru '%s'.";
$a->strings["You are now authenticated to pumpio."] = "Nyní jste přihlášen k pumpio.";
$a->strings["return to the connector page"] = "návrat ke stránce konektor";
$a->strings["Post to pumpio"] = "Příspěvek na pumpio";
$a->strings["Pump.io Import/Export/Mirror"] = "Pump.oi Import/Export/Zrcadlení";
$a->strings["pump.io username (without the servername)"] = "uživatelské jméno pump.io (bez jména serveru)";
$a->strings["pump.io servername (without \"http://\" or \"https://\" )"] = "jméno serveru pump.io  (bez \"http://\" nebo \"https://\" )";
$a->strings["Authenticate your pump.io connection"] = "Přihlásit ke spojení na pump.io";
$a->strings["Import the remote timeline"] = "Importovat vzdálenou časovou osu";
$a->strings["Enable pump.io Post Addon"] = "Povolit doplněk pumo.io Post";
$a->strings["Post to pump.io by default"] = "Posílat příspěvky na pump.io automaticky";
$a->strings["Should posts be public?"] = "Mají být příspěvky veřejné?";
$a->strings["Mirror all public posts"] = "Zrcadlit všechny veřejné příspěvky";
$a->strings["Check to delete this preset"] = "Zaškrtnout pro smazání tohoto nastavení";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Pump.io post failed. Queued for retry."] = "Zaslání příspěvku na Pump.io selhalo. Příspěvek byl zařazen do fronty pro opakované odeslání.";
$a->strings["Pump.io like failed. Queued for retry."] = "Zaslání příspěvku na Pump.io zřejmě selhalo. Příspěvek byl zařazen do fronty pro opakované odeslání.";
$a->strings["status"] = "stav";
$a->strings["%1\$s likes %2\$s's %3\$s"] = "%1\$s se líbí %3\$s %2\$s";
