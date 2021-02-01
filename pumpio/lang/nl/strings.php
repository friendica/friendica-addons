<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Permission denied."] = "Toegang geweigerd.";
$a->strings["Unable to register the client at the pump.io server '%s'."] = "Niet mogelijk om uw client te registreren op pump.io server '%s'";
$a->strings["Pump.io Import/Export/Mirror"] = "Pump.io Import/Exporteren/Spiegelen";
$a->strings["Enable pump.io Post Addon"] = "Pump.io Post Addon inschakelen";
$a->strings["Post to pump.io by default"] = "Plaatsen op pump.io als standaard instellen ";
$a->strings["Save Settings"] = "Instellingen opslaan";
