<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Select files for upload'] = 'Sélectionner les fichiers à télécharger';
$a->strings['Drop files here to upload'] = 'Glisser les fichiers ici pour uploader';
$a->strings['Cancel'] = 'Annuler';
$a->strings['Failed'] = 'Echec';
$a->strings['No files were uploaded.'] = 'Aucun fichier téléchargé';
$a->strings['Uploaded file is empty'] = 'Le fichier téléchargé est vide';
$a->strings['Image exceeds size limit of %s'] = 'L\'image dépasse la taille limite de %s';
$a->strings['File has an invalid extension, it should be one of %s.'] = 'Le fichier a une extension invalide, elle devrait être parmi celles-ci : %s.';
$a->strings['Upload was cancelled, or server error encountered'] = 'Le téléchargement a été annulé ou le server a rencontré une erreur';
