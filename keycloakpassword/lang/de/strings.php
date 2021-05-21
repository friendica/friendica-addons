<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Client ID"] = "ID des Clients";
$a->strings["Client secret"] = "Client Geheimnis";
$a->strings["The secret assigned to the OpenID Connect client you created for this addon in Keycloak."] = "Das Geheimnis, das du in Keycloak für den Client hinterlegt hast.";
$a->strings["OpenID Connect endpoint"] = "OpenID Verbindungsendpunkt";
$a->strings["Save Settings"] = "Einstellungen speichern";
