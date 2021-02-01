<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Numfriends settings updated."] = "Configuration de Numfriends mise à jour.";
$a->strings["Numfriends Settings"] = "Réglages de Numfriends";
$a->strings["How many contacts to display on profile sidebar"] = "Nombre de contacts à afficher dans le volet de profil";
$a->strings["Submit"] = "Appliquer";
