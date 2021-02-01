<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["XMPP settings updated."] = "zaktualizowano ustawienia XMPP.";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Włącz Webchat";
$a->strings["Individual Credentials"] = "Indywidualne poświadczenia";
$a->strings["Jabber BOSH host"] = "Jabber BOSH host";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Use central userbase"] = "Użyj centralnej bazy użytkowników";
$a->strings["Settings updated."] = "Ustawienia zaktualizowane.";
