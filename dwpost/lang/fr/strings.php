<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Post to Dreamwidth"] = "Publier sur Dreamwidth";
$a->strings["Dreamwidth Export"] = "Export Dreamwidth";
$a->strings["Enable dreamwidth Post Addon"] = "Activer l'application complémentaire de publication Dreamwidth.";
$a->strings["dreamwidth username"] = "Nom d'utilisateur Dreamwidth";
$a->strings["dreamwidth password"] = "Mot de passe dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Poster sur Dreamwidth par défaut";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
