<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Post to Diaspora"] = "Odeslat na Diasporu";
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = "Nelze se přihlásit na váš účet Diaspora. Prosím ověřte své uživatelské jméno a heslo a ujistěte se, že jste použili kompletní adresu (včetně http...)";
$a->strings["Diaspora Export"] = "Diaspora export";
$a->strings["Enable Diaspora Post Addon"] = "Povolit doplněk Diaspora Post";
$a->strings["Diaspora username"] = "Uživatelské jméno na Diaspora";
$a->strings["Diaspora password"] = "Heslo na Diaspora";
$a->strings["Diaspora site URL"] = "Adresa webu Diaspora";
$a->strings["Post to Diaspora by default"] = "Ve výchozím stavu zasílat příspěvky na Diaspora";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Diaspora post failed. Queued for retry."] = "Zaslání příspěvku na Diasporu selhalo. Příspěvek byl zařazen do fronty pro opakované odeslání.";
