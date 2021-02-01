<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Save Settings"] = "Enregistrer les Paramètres";
$a->strings["Allow \"good\" crawlers"] = "Autoriser les \"bons\" crawlers";
$a->strings["Block GabSocial"] = "Bloquer GabSocial";
$a->strings["Training mode"] = "Mode d'entraînement";
$a->strings["Settings updated."] = "Paramètres mis à jour.";
