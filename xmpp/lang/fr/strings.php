<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Activer le chat en ligne";
$a->strings["Individual Credentials"] = "Identification individuelle";
$a->strings["Jabber BOSH host"] = "Hôte Jabber BOSH";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Use central userbase"] = "Utilisez la base de données centrale d'utilisateurs";
