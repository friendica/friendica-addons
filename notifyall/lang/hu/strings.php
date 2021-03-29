<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["%s Administrator"] = "A(z) %s adminisztrátora";
$a->strings["%1\$s, %2\$s Administrator"] = "%1\$s, a(z) %2\$s adminisztrátora";
$a->strings["Send email to all members"] = "E-mail küldése az összes tagnak";
$a->strings["No recipients found."] = "Nem találhatók címzettek.";
$a->strings["Emails sent"] = "E-mailek elküldve";
$a->strings["Send email to all members of this Friendica instance."] = "E-mail küldése ezen Friendica példány összes tagjának.";
$a->strings["Message subject"] = "Üzenet tárgya";
$a->strings["Test mode (only send to administrator)"] = "Tesztmód (küldés csak az adminisztrátornak)";
$a->strings["Submit"] = "Elküldés";
