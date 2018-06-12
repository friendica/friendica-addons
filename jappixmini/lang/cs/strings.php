<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Jappix Mini addon settings"] = "Nastavení rozšíření Jappix Mini";
$a->strings["Activate addon"] = "Aktivovat doplněk";
$a->strings["Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface"] = "<em>Nevkládejte</em> Jappixmini Chat-Widget do webového rozhraní";
$a->strings["Jabber username"] = "Jabber uživatelské jméno";
$a->strings["Jabber server"] = "Jabber server";
$a->strings["Jabber BOSH host"] = "Jabber BOSH host";
$a->strings["Jabber password"] = "Jabber heslo";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Zašifrovat Jabber heslo s heslem Friendica (doporučeno)";
$a->strings["Friendica password"] = "Friendica heslo";
$a->strings["Approve subscription requests from Friendica contacts automatically"] = "Automaticky schválit požadavek na členství od Friedica kontaktů.";
$a->strings["Subscribe to Friendica contacts automatically"] = "Automaticky zaslat požadavek na členství Friedica kontaktům.";
$a->strings["Purge internal list of jabber addresses of contacts"] = "Očistit interní seznam jabber adres kontaktů";
$a->strings["Submit"] = "Odeslat";
$a->strings["Add contact"] = "Přidat kontakt";
