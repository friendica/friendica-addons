<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Upload a file"] = "Téléverser un fichier";
$a->strings["Drop files here to upload"] = "Glisser les fichiers ici pour uploader";
$a->strings["Cancel"] = "Annuler";
