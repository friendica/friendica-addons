<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["XMPP settings updated."] = "XMPP Einstellungen aktualisiert.";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Aktiviere Webchat";
$a->strings["Individual Credentials"] = "Individuelle Anmeldedaten";
$a->strings["Jabber BOSH host"] = "Jabber BOSH Host";
$a->strings["Save Settings"] = "Speichere Einstellungen";
$a->strings["Use central userbase"] = "Nutze zentrale Nutzerbasis";
$a->strings["Settings updated."] = "Einstellungen aktualisiert.";
