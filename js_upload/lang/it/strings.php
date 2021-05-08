<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Select files for upload"] = "Seleziona file per il caricamento";
$a->strings["Drop files here to upload"] = "Trascina un file qui per caricarlo";
$a->strings["Cancel"] = "Annulla";
$a->strings["Failed"] = "Caricamento fallito";
$a->strings["No files were uploaded."] = "Nessun file è stato caricato.";
$a->strings["Uploaded file is empty"] = "Il file caricato è vuoto";
$a->strings["Image exceeds size limit of %s"] = "La dimensione dell'immagine supera il limite di %s";
$a->strings["File has an invalid extension, it should be one of %s."] = "Il file ha un'estensione non valida, dovrebbe essere una tra %s.";
$a->strings["Upload was cancelled, or server error encountered"] = "Il caricamento è stato cancellato, o si è verificato un errore sul server";
