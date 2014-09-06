<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Post to Diaspora"] = "Příspěvek na Diaspora";
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = "Nelze se přihlásit na váš Diaspora účet. Prosím ověřte své uživatelské jméno a heslo a ujistěte se, že jste použili kompletní adresu (včetně http...)";
$a->strings["Diaspora Export"] = "Diaspora export";
$a->strings["Enable Diaspora Post Plugin"] = "Povolit Diaspora Plugin";
$a->strings["Diaspora username"] = "Diaspora uživatelské jméno";
$a->strings["Diaspora password"] = "Diaspora heslo";
$a->strings["Diaspora site URL"] = "Adresa webu Diaspora";
$a->strings["Post to Diaspora by default"] = "Defaultně zasílat příspěvky na Diaspora";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Diaspora post failed. Queued for retry."] = "Zaslání příspěvku na Diasporu selhalo. Příspěvek byl zařazen do fronty pro opakované odeslání.";
