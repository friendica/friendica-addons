<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["XMPP settings updated."] = "Nastavení XMPP aktualizována";
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Povolit Webchat";
$a->strings["Individual Credentials"] = "Jednotlivé kredenciály";
$a->strings["Jabber BOSH host"] = "BOSH host Jabber";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Use central userbase"] = "Použít centrální uživatelskou základnu";
$a->strings["If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the \"auth_ejabberd.php\" script."] = "Pokud tuto funkci povolíte, uživatelé budou automaticky přihlášeni na server ejabberd, který musí být nainstalovaný na tomto serveru se synchronizovanými kredenciálami přes skript \"auth_ejabberd.php\".";
$a->strings["Settings updated."] = "Nastavení aktualizována";
