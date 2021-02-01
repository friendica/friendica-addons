<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Permission denied."] = "Åtkomst nekad.";
$a->strings["Save Settings"] = "Spara inställningar";
$a->strings["Client ID"] = "Klient-ID";
$a->strings["Client Secret"] = "Klient hemlig nyckel";
$a->strings["Error when registering buffer connection:"] = "Fel vid anslutning till Buffer";
$a->strings["You are now authenticated to buffer. "] = "Du är nu autentiserad mot Buffer.";
$a->strings["return to the connector page"] = "återgå till anslutningssida";
$a->strings["Post to Buffer"] = "Inlägg till Buffer";
$a->strings["Buffer Export"] = "Export till Buffer";
$a->strings["Authenticate your Buffer connection"] = "Validera din anslutning mot Buffer";
$a->strings["Enable Buffer Post Addon"] = "Aktivera tillägg för Buffer-inlägg";
$a->strings["Post to Buffer by default"] = "Lägg in på Buffer som standard";
$a->strings["Check to delete this preset"] = "Markera för att ta bort förinställning";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Inlägg skickas som standard till alla konton som är aktiverade:";
