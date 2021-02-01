<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Send email to all members"] = "Enviar email a todos los miembros";
$a->strings["%s Administrator"] = "%s Administrador";
$a->strings["%1\$s, %2\$s Administrator"] = "%1\$s, %2\$s Administrador";
$a->strings["No recipients found."] = "No se encontraron destinatarios.";
$a->strings["Emails sent"] = "Emails enviados";
$a->strings["Send email to all members of this Friendica instance."] = "Enviar email a todos los miembros de esta instancia de Friendica.";
$a->strings["Message subject"] = "Tema del mensaje";
$a->strings["Test mode (only send to administrator)"] = "Modo de prueba (sólo envíar al administrador)";
$a->strings["Submit"] = "Enviar";
