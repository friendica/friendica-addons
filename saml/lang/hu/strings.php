<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Settings statement"] = "Beállítások nyilatkozata";
$a->strings["IdP ID"] = "IdP-azonosító";
$a->strings["Client ID"] = "Ügyfél-azonosító";
$a->strings["IdP SSO URL"] = "IdP SSO URL";
$a->strings["IdP SLO request URL"] = "IdP SLO kérés URL";
$a->strings["IdP SLO response URL"] = "IdP SLO válasz URL";
$a->strings["SP private key"] = "Szolgáltató személyes kulcsa";
$a->strings["SP certificate"] = "Szolgáltató tanúsítványa";
$a->strings["The certficate for the addon's private key."] = "A tanúsítvány a bővítmény személyes kulcsához.";
$a->strings["IdP certificate"] = "IdP tanúsítvány";
$a->strings["The x509 certficate for your identity provider."] = "A személyazonosság-szolgáltatójának x509 tanúsítványa.";
$a->strings["Save Settings"] = "Beállítások mentése";
