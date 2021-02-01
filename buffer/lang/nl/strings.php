<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Permission denied."] = "Toegang geweigerd.";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Client ID"] = "Cliënt ID";
$a->strings["Client Secret"] = "Cliënt geheim";
$a->strings["Error when registering buffer connection:"] = "Fout bij het registreren van de buffer verbinding:";
$a->strings["You are now authenticated to buffer. "] = "Je bent nu aangemeld bij Buffer";
$a->strings["return to the connector page"] = "ga terug naar de verbindingspagina";
$a->strings["Post to Buffer"] = "Plaats bericht op Buffer";
$a->strings["Buffer Export"] = "Buffer Exporteren";
$a->strings["Authenticate your Buffer connection"] = "Verbinding met Buffer goedkeuren";
$a->strings["Enable Buffer Post Addon"] = "Buffer Post Addon inschakelen";
$a->strings["Post to Buffer by default"] = "Plaatsen op Buffer als standaard instellen";
$a->strings["Check to delete this preset"] = "Vink aan om deze vooraf ingestelde opties te verwijderen ";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Berichten gaan naar alle accounts die als standaard zijn ingeschakeld: ";
