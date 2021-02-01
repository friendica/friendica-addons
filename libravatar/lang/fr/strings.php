<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["generic profile image"] = "image de profil générique";
$a->strings["computer generated face"] = "visage généré par ordinateur";
$a->strings["Warning"] = "Attention";
$a->strings["Your PHP version %s is lower than the required PHP >= 5.3."] = "Votre version de PHP %s est inférieure à la minimum requise (5.3).";
$a->strings["Information"] = "Information";
$a->strings["Submit"] = "Envoyer";
$a->strings["Default avatar image"] = "Avatar par défaut";
$a->strings["Libravatar settings updated."] = "Paramètres de Libravatar mis à jour.";
