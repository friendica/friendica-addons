<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Administrator"] = "Administrátor";
$a->strings["Your account on %s will expire in a few days."] = "Platnost Vašeho účtu na %s vyprší během několika dní.";
$a->strings["Your Friendica account is about to expire."] = "Váš účet na Frendica brzy vyprší.";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "Dobrý den, %1\$s,\n\nVáš účet na %2\$s vyprší za méně než pět dní. Svůj účet si zachováte, pokud se přihlásíte alespoň jednou za každých 30 dní.";
