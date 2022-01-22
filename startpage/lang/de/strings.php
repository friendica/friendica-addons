<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Home page to load after login  - leave blank for profile wall'] = 'Seite, die nach dem Anmelden geladen werden soll. Leer = Pinnwand';
$a->strings['Examples: "network" or "notifications/system"'] = 'Beispiele: "network" oder "notifications/system"';
$a->strings['Startpage'] = 'Startpage';
