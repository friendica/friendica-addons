<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["XMPP settings updated."] = "XMPP-asetukset päivitetty";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Ota Webchat käyttöön";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Settings updated."] = "Asetukset päivitetty.";
