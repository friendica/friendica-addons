<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Jappix Mini"] = "Jappix Mini";
$a->strings["Activate addon"] = "Bővítmény bekapcsolása";
$a->strings["Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface"] = "<em>Ne</em> szúrja be a Jappixmini csevegés felületi elemet a webes felületbe";
$a->strings["Jabber username"] = "Jabber felhasználónév";
$a->strings["Jabber server"] = "Jabber kiszolgáló";
$a->strings["Jabber BOSH host"] = "Jabber BOSH kiszolgáló";
$a->strings["Jabber password"] = "Jabber jelszó";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Jabber jelszó titkosítása a Friendica jelszóval (ajánlott)";
$a->strings["Friendica password"] = "Friendica jelszó";
$a->strings["Approve subscription requests from Friendica contacts automatically"] = "A Friendica partnerektől érkező feliratkozási kérelmek automatikus jóváhagyása";
$a->strings["Subscribe to Friendica contacts automatically"] = "Automatikus feliratkozás a Friendica partnerekre";
$a->strings["Purge internal list of jabber addresses of contacts"] = "A partnerek jabber-címei belső listájának törlése";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Add contact"] = "Partner hozzáadása";
