<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-csevegés (Jabber)";
$a->strings["Enable Webchat"] = "Webes csevegés engedélyezése";
$a->strings["Individual Credentials"] = "Egyéni hitelesítési adatok";
$a->strings["Jabber BOSH host"] = "Jabber BOSH kiszolgáló";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Use central userbase"] = "Központi felhasználóbázis használata";
