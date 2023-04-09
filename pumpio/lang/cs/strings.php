<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Permission denied.'] = 'Přístup odmítnut.';
$a->strings['Unable to register the client at the pump.io server \'%s\'.'] = 'Nebylo možné registrovat klienta na serveru pump.io "%s".';
$a->strings['You are now authenticated to pumpio.'] = 'Nyní jste přihlášen/a na pump.io.';
$a->strings['return to the connector page'] = 'návrat na stránku konektoru';
$a->strings['Post to pumpio'] = 'Posílat na pump.io';
$a->strings['Save Settings'] = 'Uložit nastavení';
$a->strings['Authenticate your pump.io connection'] = 'Přihlásit ke spojení na pump.io';
$a->strings['Import the remote timeline'] = 'Importovat vzdálenou časovou osu';
$a->strings['Should posts be public?'] = 'Mají být příspěvky veřejné?';
$a->strings['Mirror all public posts'] = 'Zrcadlit všechny veřejné příspěvky';
$a->strings['Pump.io Import/Export/Mirror'] = 'Import/Export/Zrcadlení Pump.io';
$a->strings['status'] = 'stav';
$a->strings['%1$s likes %2$s\'s %3$s'] = 'Uživateli %1$s se líbí %3$s uživatele %2$s';
