<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["XMPP settings updated."] = "XMPP-instellingen opgeslagen";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-chat (Jabber)";
$a->strings["Enable Webchat"] = "Webchat inschakelen";
$a->strings["Individual Credentials"] = "Individuele inloggegevens";
$a->strings["Jabber BOSH host"] = "Jabber BOSH Server";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Use central userbase"] = "Gebruik centrale gebruikersbank";
$a->strings["Settings updated."] = "Instellingen opgeslagen";
