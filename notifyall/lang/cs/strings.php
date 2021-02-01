<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Send email to all members"] = "Odeslat e-mail všem členům";
$a->strings["%s Administrator"] = "%s Administrátor";
$a->strings["%1\$s, %2\$s Administrator"] = "%1\$s, %2\$s Administrátor";
$a->strings["No recipients found."] = "Nenalezeni žádní příjemci.";
$a->strings["Emails sent"] = "E-maily odeslány";
$a->strings["Send email to all members of this Friendica instance."] = "Odeslat e-mail všem členům této instance Friendica.";
$a->strings["Message subject"] = "Předmět zprávy";
$a->strings["Test mode (only send to administrator)"] = "Testovací režim (odeslat pouze administrátorovi)";
$a->strings["Submit"] = "Odeslat";
