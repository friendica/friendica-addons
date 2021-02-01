<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["From Address"] = "Desde Dirección";
$a->strings["Email address that stream items will appear to be from."] = "Aparecerá de dónde viene la dirección de email que transmite artículos";
$a->strings["Save Settings"] = "Guardar ajustes";
$a->strings["Re:"] = "Re:";
$a->strings["Friendica post"] = "Entrada de Friendica";
$a->strings["Diaspora post"] = "Entrada de Diaspora";
$a->strings["Feed item"] = "Surtir artículo";
$a->strings["Email"] = "Email";
$a->strings["Friendica Item"] = "Artículo de Friendica";
$a->strings["Upstream"] = "Contracorriente";
$a->strings["Local"] = "Local";
$a->strings["Email Address"] = "Dirección de email";
$a->strings["Leave blank to use your account email address"] = "Dejar en blanco para usar su cuenta de dirección de email";
$a->strings["Enabled"] = "Habilitado";
$a->strings["Mail Stream Settings"] = "Ajustes de transmisión de Mail";
