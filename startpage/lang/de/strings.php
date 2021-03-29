<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Startpage"] = "Startpage";
$a->strings["Home page to load after login  - leave blank for profile wall"] = "Seite, die nach dem Anmelden geladen werden soll. Leer = Pinnwand";
$a->strings["Examples: &quot;network&quot; or &quot;notifications/system&quot;"] = "Beispiele: network, notifications/system";
$a->strings["Save Settings"] = "Einstellungen speichern";
