<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["XMPP settings updated."] = "Nastavení XMPP aktualizována";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Povolit Webchat";
$a->strings["Individual Credentials"] = "Jednotlivé kredenciály";
$a->strings["Jabber BOSH host"] = "BOSH host Jabber";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Use central userbase"] = "Použít centrální uživatelskou základnu";
$a->strings["Settings updated."] = "Nastavení aktualizována";
