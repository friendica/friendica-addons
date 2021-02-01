<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Jappix Mini addon settings"] = "Ustawienia dodatku Jappix Mini";
$a->strings["Activate addon"] = "Aktywuj dodatek";
$a->strings["Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface"] = "<em>Nie</em> wstawiaj widżetu czatu Jappixmini do interfejsu WWW";
$a->strings["Jabber username"] = "Nazwa użytkownika Jabber";
$a->strings["Jabber server"] = "Serwer Jabber";
$a->strings["Jabber BOSH host"] = "Host Jabber BOSH";
$a->strings["Jabber password"] = "Hasło Jabber";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Szyfrowanie hasła Jabbera za pomocą hasła Friendica (zalecane)";
$a->strings["Friendica password"] = "Hasło Friendica";
$a->strings["Approve subscription requests from Friendica contacts automatically"] = "Subskrybuj automatycznie kontakty z Friendica";
$a->strings["Subscribe to Friendica contacts automatically"] = "Subskrybuj kontakty z Friendica automatycznie";
$a->strings["Purge internal list of jabber addresses of contacts"] = "Usuń wewnętrzną listę adresów Jabber z kontaktów";
$a->strings["Submit"] = "Wyślij";
$a->strings["Add contact"] = "Dodaj kontakt";
