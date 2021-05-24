<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Settings statement"] = "Anweisungen zu den Einstellungen";
$a->strings["IdP ID"] = "IdP ID";
$a->strings["Client ID"] = "Client-ID";
$a->strings["IdP SSO URL"] = "IdP SSO URL";
$a->strings["The URL for your identity provider's SSO endpoint."] = "Die URL des SSO-Endpunktes deines Identitätenanbieters.";
$a->strings["IdP SLO request URL"] = "IdP-SLO-Anfrage-URL";
$a->strings["The URL for your identity provider's SLO request endpoint."] = "Die URL des SLO-Anfrage-Endpunktes deines Identitätenanbieters.";
$a->strings["IdP SLO response URL"] = "IdP-SLO-Antwort-URL";
$a->strings["The URL for your identity provider's SLO response endpoint."] = "Die URL des SLO-Antwort-Endpunktes deines Identitätenanbieters.";
$a->strings["SP private key"] = "Privater Schlüssel (SP)";
$a->strings["SP certificate"] = "SP-Zertifikat";
$a->strings["The certficate for the addon's private key."] = "Das Zertifikat für den privaten Schlüssel des Addons.";
$a->strings["IdP certificate"] = "IdP-Zertifikat";
$a->strings["The x509 certficate for your identity provider."] = "Das x509-Zertifikat deines Identitätanbieters.";
$a->strings["Save Settings"] = "Einstellungen speichern";
