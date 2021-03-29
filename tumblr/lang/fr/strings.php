<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Permission denied."] = "Permission refusée.";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Consumer Key"] = "Clé utilisateur";
$a->strings["Consumer Secret"] = "Secret utilisateur";
$a->strings["You are now authenticated to tumblr."] = "Vous êtes maintenant identifié sur Tumblr";
$a->strings["return to the connector page"] = "Revenir à la page de connexion";
$a->strings["Post to Tumblr"] = "Publier vers Tumblr";
$a->strings["Tumblr Export"] = "Exporter vers Tumblr";
$a->strings["(Re-)Authenticate your tumblr page"] = "(re)Authentifiez votre page Tumblr";
$a->strings["Enable Tumblr Post Addon"] = "Activez l'extension de publication Tumblr";
$a->strings["Post to Tumblr by default"] = "Publier sur Tumblr par défaut";
$a->strings["Post to page:"] = "Publier sur la page :";
$a->strings["You are not authenticated to tumblr"] = "Vous n'êtes pas identifié sur Tumblr";
