<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Diaspora"] = "Publicar a diàspora";
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = "No es pot iniciar la sessió al vostre compte de Diaspora. Comproveu nom d'usuari i contrasenya i assegureu-vos que heu utilitzat l'adreça completa (inclosa http ...)";
$a->strings["Diaspora Export"] = "Exportació de diàspora";
$a->strings["Enable Diaspora Post Addon"] = "Habilita Addon Post de Diaspora";
$a->strings["Diaspora username"] = "Nom d'usuari de diàspora";
$a->strings["Diaspora password"] = "Contrasenya de diàspora";
$a->strings["Diaspora site URL"] = "URL del lloc de diàspora";
$a->strings["Post to Diaspora by default"] = "Publica a Diaspora de manera predeterminada";
$a->strings["Save Settings"] = "Desa la configuració";
$a->strings["Diaspora post failed. Queued for retry."] = "La publicació de la diàspora ha fallat Feu cua per tornar a provar.";
