<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Permission denied."] = "Přístup odmítnut.";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Client ID"] = "Client ID";
$a->strings["Client Secret"] = "Client Secret";
$a->strings["Error when registering buffer connection:"] = "Chyba při registraci připojení na buffer:";
$a->strings["You are now authenticated to buffer. "] = "Nyní jste přihlášen/a na buffer.";
$a->strings["return to the connector page"] = "zpět ke stránce konektoru";
$a->strings["Post to Buffer"] = "Posílat na Buffer";
$a->strings["Buffer Export"] = "Buffer Export";
$a->strings["Authenticate your Buffer connection"] = "Autentikujte své připojení na Buffer";
$a->strings["Enable Buffer Post Addon"] = "Povolit doplněk Buffer Post";
$a->strings["Post to Buffer by default"] = "Ve výchozím stavu posílat na Buffer";
$a->strings["Check to delete this preset"] = "Zaškrtnutím smažete toto nastavení";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Příspěvky budou posílány na všechny účty, které jsou ve výchozím stavu povoleny:";
