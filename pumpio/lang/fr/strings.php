<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Permission denied."] = "Permission refusée.";
$a->strings["You are now authenticated to pumpio."] = "Vous êtes maintenant authentifié sur pump.io.";
$a->strings["return to the connector page"] = "Retourner à la page du connecteur";
$a->strings["Post to pumpio"] = "Publier sur pump.io";
$a->strings["pump.io username (without the servername)"] = "Nom d'utilisateur pump.io (sans le nom du serveur)";
$a->strings["Import the remote timeline"] = "Importer la timeline distante";
$a->strings["Post to pump.io by default"] = "Publier sur pump.io par défaut";
$a->strings["Should posts be public?"] = "Les messages devraient être publiques ?";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["status"] = "statut";
