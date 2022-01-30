<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Åtkomst nekad.';
$a->strings['You are now authenticated to pumpio.'] = 'Du är nu autentiserad till pumpio.';
$a->strings['Save Settings'] = 'Spara inställningar';
$a->strings['Authenticate your pump.io connection'] = 'Autentisera din pum.io-anslutning';
$a->strings['Should posts be public?'] = 'Bör inlägg vara publika?';
$a->strings['Mirror all public posts'] = 'Spegla alla publika inlägg';
$a->strings['status'] = 'status';
$a->strings['%1$s likes %2$s\'s %3$s'] = '%1$s gillar %2$s\'s %3$s';
