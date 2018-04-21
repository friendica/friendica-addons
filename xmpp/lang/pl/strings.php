<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["XMPP settings updated."] = "zaktualizowano ustawienia XMPP.";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Włącz Webchat";
$a->strings["Individual Credentials"] = "Indywidualne poświadczenia";
$a->strings["Jabber BOSH host"] = "Jabber BOSH host";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Use central userbase"] = "Użyj centralnej bazy użytkowników";
$a->strings["If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the \"auth_ejabberd.php\" script."] = "Jeśli jest włączona, użytkownicy automatycznie logują się do serwera ejabberd, który musi być zainstalowany na tym komputerze z synchronizowanymi danymi uwierzytelniającymi za pomocą skryptu \"auth_ejabberd.php\".";
$a->strings["Settings updated."] = "Ustawienia zaktualizowane.";
