<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return ($n > 1);;
}}
;
$a->strings["Post to blogger"] = "Poster sur Blogger";
$a->strings["Blogger Export"] = "Export Blogger";
$a->strings["Enable Blogger Post Addon"] = "Activer l'extension de publication Blogger";
$a->strings["Blogger username"] = "Nom d'utilisateur Blogger";
$a->strings["Blogger password"] = "Mot de passe Blogger";
$a->strings["Blogger API URL"] = "URL de l'API de Blogger";
$a->strings["Post to Blogger by default"] = "Poster sur Blogger par défaut";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Post from Friendica"] = "Publier depuis Friendica";
