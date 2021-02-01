<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Permission denied."] = "Zugriff verweigert.";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Client ID"] = "Client ID";
$a->strings["Client Secret"] = "Client Secret";
$a->strings["Error when registering buffer connection:"] = "Fehler beim Registrieren des Buffer-Connectors.";
$a->strings["You are now authenticated to buffer. "] = "Du bist nun auf Buffer authentifiziert.";
$a->strings["return to the connector page"] = "zurück zur Connector-Seite";
$a->strings["Post to Buffer"] = "Auf Buffer veröffentlichen";
$a->strings["Buffer Export"] = "Buffer Export";
$a->strings["Authenticate your Buffer connection"] = "Authentifiziere deine Verbindung zu Buffer";
$a->strings["Enable Buffer Post Addon"] = "Buffer-Post-Addon aktivieren";
$a->strings["Post to Buffer by default"] = "Standardmäßig auf Buffer veröffentlichen";
$a->strings["Check to delete this preset"] = "Markieren, um diese Voreinstellung zu löschen";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Beiträge werden an alle Accounts geschickt, die standardmäßig aktiviert sind.";
