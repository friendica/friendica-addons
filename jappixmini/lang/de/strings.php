<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Jappix Mini"] = "Jappix Mini";
$a->strings["Activate addon"] = "Addon aktivieren";
$a->strings["Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface"] = "Füge das Jappix-Mini-Chat-Widget <em>nicht</em> zum Webinterface hinzu";
$a->strings["Jabber username"] = "Jabber-Nutzername";
$a->strings["Jabber server"] = "Jabber-Server";
$a->strings["Jabber BOSH host"] = "Jabber-BOSH-Host";
$a->strings["Jabber password"] = "Jabber-Passwort";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Verschlüssele das Jabber-Passwort mit dem Friendica-Passwort (empfohlen)";
$a->strings["Friendica password"] = "Friendica-Passwort";
$a->strings["Approve subscription requests from Friendica contacts automatically"] = "Kontaktanfragen von Friendica-Kontakten automatisch akzeptieren";
$a->strings["Subscribe to Friendica contacts automatically"] = "Automatisch Friendica-Kontakten bei Jabber folgen";
$a->strings["Purge internal list of jabber addresses of contacts"] = "Lösche die interne Liste der Jabber-Adressen der Kontakte";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Add contact"] = "Kontakt hinzufügen";
