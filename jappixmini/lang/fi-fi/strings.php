<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Jappix Mini addon settings"] = "Jappix Mini -lisäosan asetukset";
$a->strings["Activate addon"] = "Ota lisäosa käyttöön";
$a->strings["Jabber username"] = "Jabber -käyttäjätunnus";
$a->strings["Jabber server"] = "Jabber -palvelin";
$a->strings["Jabber BOSH host"] = "Jabber BOSH-palvelin";
$a->strings["Jabber password"] = "Jabber -salasana";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Salaa Jabber -salasana Friendica -salasanalla (suositeltava)";
$a->strings["Friendica password"] = "Friendica -salasana";
$a->strings["Approve subscription requests from Friendica contacts automatically"] = "Hyväksy automaattisesti tilauspyynnöt Friendica -kontakteilta";
$a->strings["Submit"] = "Lähetä";
$a->strings["Add contact"] = "Lisää kontakti";
