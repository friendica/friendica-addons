<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["XMPP-Chat (Jabber)"] = "XMPP-Chat (Jabber)";
$a->strings["Enable Webchat"] = "Habilitar Webchat";
$a->strings["Individual Credentials"] = "Credenciales individuales";
$a->strings["Jabber BOSH host"] = "Jabber BOSH host";
$a->strings["Save Settings"] = "Guardar ajustes";
$a->strings["Use central userbase"] = "Utilice la base de usuarios central";
