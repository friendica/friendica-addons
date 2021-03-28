<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Geonames Settings"] = "Paramètres Geonames";
$a->strings["Replace numerical coordinates by the nearest populated location name in your posts."] = "Remplacer les coordonnées par le nom de la localité la plus proche dans votre publication.";
$a->strings["Enable Geonames Addon"] = "Activer l'application complémentaire Geonames";
$a->strings["Save Settings"] = "Sauvegarder les paramètres.";
