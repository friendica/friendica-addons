<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["\"Secure Mail\" Settings"] = "Nastavení \"Secure Mail\"";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Save and send test"] = "";
$a->strings["Enable Secure Mail"] = "";
$a->strings["Public key"] = "";
$a->strings["Your public PGP key, ascii armored format"] = "";
$a->strings["Secure Mail Settings saved."] = "";
$a->strings["Test email sent"] = "";
$a->strings["There was an error sending the test email"] = "";
