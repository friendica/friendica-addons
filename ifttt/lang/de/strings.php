<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["IFTTT Mirror"] = "IFTTT Spiegel";
$a->strings["Body for \"new status message\""] = "Body für \"Neue Status Meldung\"";
$a->strings["Body for \"new photo upload\""] = "Body für \"Neues Foto hochgeladen\"";
$a->strings["Body for \"new link post\""] = "Body für \"Neue Nachricht\"";
$a->strings["Generate new key"] = "Generiere neuen Schlüssel";
$a->strings["Save Settings"] = "Speichere Einstellungen";
