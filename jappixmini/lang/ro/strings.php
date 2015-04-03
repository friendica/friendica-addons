<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Jappix Mini addon settings"] = "Configurare addon Mini Jappix";
$a->strings["Activate addon"] = "Activare supliment";
$a->strings["Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface"] = "<em>Nu</em> se introduce Jappixmini Chat-Widget în interfața web";
$a->strings["Jabber username"] = "Utilizator Jabber ";
$a->strings["Jabber server"] = "Server Jabber ";
$a->strings["Jabber BOSH host"] = "Host BOSH Jabber";
$a->strings["Jabber password"] = "Parolă Jabber ";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Criptare parolă Jabber cu parola Friendica (recomandat)";
$a->strings["Friendica password"] = "Parolă Friendica ";
$a->strings["Approve subscription requests from Friendica contacts automatically"] = "Aprobare solicitări de subscriere din contactele Friendica, în mod automat";
$a->strings["Subscribe to Friendica contacts automatically"] = "Abonare automată la contactele Friendica";
$a->strings["Purge internal list of jabber addresses of contacts"] = "Curățare listă internă a adreselor de contact jabber";
$a->strings["Submit"] = "Trimite";
$a->strings["Add contact"] = "Adăugare contact";
