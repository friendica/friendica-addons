<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Drop files here to upload'] = 'Sleep uw bestanden hier om ze te uploaden';
$a->strings['Cancel'] = 'Afbreken';
$a->strings['Failed'] = 'Mislukt';
$a->strings['No files were uploaded.'] = 'Er waren geen bestanden geüpload.';
$a->strings['Uploaded file is empty'] = 'Het geüploade bestand is leeg';
