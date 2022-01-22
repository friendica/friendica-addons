<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Drop files here to upload'] = 'Släpp filer här för att ladda upp';
$a->strings['Cancel'] = 'Avbryt';
$a->strings['Failed'] = 'Misslyckades';
$a->strings['No files were uploaded.'] = 'Inga filer laddades upp.';
$a->strings['Uploaded file is empty'] = 'Den uppladdade filen är tom';
$a->strings['Upload was cancelled, or server error encountered'] = 'Uppladdningen avbröts, eller så uppstod det ett server-fel';
