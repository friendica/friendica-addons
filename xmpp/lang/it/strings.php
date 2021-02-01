<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["XMPP settings updated."] = "Impostazioni XMPP aggiornate.";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Abilita chat web";
$a->strings["Individual Credentials"] = "Credenziali Individuali";
$a->strings["Jabber BOSH host"] = "Server Jabber BOSH";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Use central userbase"] = "Usa base utenti centrale";
$a->strings["Settings updated."] = "Impostazioni aggiornate.";
