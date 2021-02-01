<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Send email to all members"] = "Lähetä sähköposti kaikille jäsenille";
$a->strings["%s Administrator"] = "%s-ylläpitäjä";
$a->strings["%1\$s, %2\$s Administrator"] = "%1\$s, %2\$s-ylläpitäjä";
$a->strings["No recipients found."] = "Vastaanottajaa ei löytynyt.";
$a->strings["Emails sent"] = "Sähköpostit lähetetty";
$a->strings["Message subject"] = "Viestin aihe";
$a->strings["Submit"] = "Lähetä";
