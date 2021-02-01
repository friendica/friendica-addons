<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["generic profile image"] = "image de profil générique";
$a->strings["random geometric pattern"] = "Schéma géométrique aléatoire ";
$a->strings["computer generated face"] = "visage généré par ordinateur";
$a->strings["Information"] = "Information";
$a->strings["Submit"] = "Envoyer";
$a->strings["Default avatar image"] = "Image par défaut d'avatar";
$a->strings["Gravatar settings updated."] = "Paramètres de Gravatar mis à jour.";
