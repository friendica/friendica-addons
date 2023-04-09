<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Select files for upload'] = 'Seleciona archivos a subir';
$a->strings['Drop files here to upload'] = 'Soltar archivos aquí para subir';
$a->strings['Cancel'] = 'Cancelar';
$a->strings['Failed'] = 'Fallido';
$a->strings['No files were uploaded.'] = 'No se subió ningún archivo.';
$a->strings['Uploaded file is empty'] = 'El archivo subido está vacío';
$a->strings['Upload was cancelled, or server error encountered'] = 'La subida fue cancelada, o el servidor tuvo un error';
