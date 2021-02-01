<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Administrator"] = "Amministratore";
$a->strings["Your account on %s will expire in a few days."] = "Il tuo account su %s scadrà tra pochi giorni.";
$a->strings["Your Friendica account is about to expire."] = "Il tuo account Friendica sta per scadere.";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "Ciao %1\$s,\n\nIl tuo account su %2\$s scadrà in meno di cinque giorni. Puoi mantenere il tuo account autenticandoti almeno una volta ogni 30 giorni";
