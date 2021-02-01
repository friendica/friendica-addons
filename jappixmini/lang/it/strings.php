<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Jappix Mini addon settings"] = "Impostazioni plugin Jappix Mini";
$a->strings["Activate addon"] = "Abilita plugin";
$a->strings["Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface"] = "Non inserire Jappixmini nell'intrerfaccia web";
$a->strings["Jabber username"] = "Nome utente Jabber";
$a->strings["Jabber server"] = "Server Jabber";
$a->strings["Jabber BOSH host"] = "Server BOSH Jabber";
$a->strings["Jabber password"] = "Password Jabber";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Cripta la password Jabber con la password di Friendica (consigliato)";
$a->strings["Friendica password"] = "Password Friendica";
$a->strings["Approve subscription requests from Friendica contacts automatically"] = "Approva le richieste di collegamento dai tuoi contatti Friendica automaticamente";
$a->strings["Subscribe to Friendica contacts automatically"] = "Sottoscrivi automaticamente i contatti Friendica";
$a->strings["Purge internal list of jabber addresses of contacts"] = "Pulisci la lista interna degli indirizzi Jabber dei contatti";
$a->strings["Submit"] = "Invia";
$a->strings["Add contact"] = "Aggiungi contatto";
