<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Select files for upload'] = 'Dateien für den Upload auswählen';
$a->strings['Drop files here to upload'] = 'Ziehe die Dateien hierher, die du hochladen willst';
$a->strings['Cancel'] = 'Abbrechen';
$a->strings['Failed'] = 'Fehlgeschlagen';
$a->strings['No files were uploaded.'] = 'Keine Dateien hochgeladen.';
$a->strings['Uploaded file is empty'] = 'Hochgeladene Datei ist leer';
$a->strings['Image exceeds size limit of '] = 'Die Bildgröße übersteigt das Limit von ';
$a->strings['File has an invalid extension, it should be one of '] = 'Die Dateiextension ist nicht erlaubt, sie muss eine der folgenden sein ';
$a->strings['Upload was cancelled, or server error encountered'] = 'Upload abgebrochen oder Serverfehler aufgetreten';
