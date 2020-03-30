<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return ($n > 1);;
}}
;
$a->strings["Upload a file"] = "Téléverser un fichier";
$a->strings["Drop files here to upload"] = "Glisser les fichiers ici pour uploader";
$a->strings["Cancel"] = "Annuler";
$a->strings["Failed"] = "";
$a->strings["No files were uploaded."] = "";
$a->strings["Uploaded file is empty"] = "";
$a->strings["Image exceeds size limit of "] = "";
$a->strings["File has an invalid extension, it should be one of "] = "";
$a->strings["Upload was cancelled, or server error encountered"] = "";
