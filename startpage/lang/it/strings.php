<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Home page to load after login  - leave blank for profile wall'] = 'Home page da caricare dopo il login - lasciare in bianco per la bacheca';
$a->strings['Examples: "network" or "notifications/system"'] = 'Esempi: "rete" o "notifiche/sistema"';
$a->strings['Startpage'] = 'Pagina iniziale';
