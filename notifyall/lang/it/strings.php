<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["%s Administrator"] = "Amministratore %s";
$a->strings["%1\$s, %2\$s Administrator"] = "%1\$s,  amministratore di %2\$s";
$a->strings["Send email to all members"] = "Invia email a tutti i membri";
$a->strings["No recipients found."] = "Nessun destinatario trovato.";
$a->strings["Emails sent"] = "Email inviate";
$a->strings["Send email to all members of this Friendica instance."] = "Invia email a tutti i membri di questa istanza Friendica.";
$a->strings["Message subject"] = "Oggetto del messaggio";
$a->strings["Test mode (only send to administrator)"] = "Modalità test (invia solo agli amministratori)";
$a->strings["Submit"] = "Invia";
