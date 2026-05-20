<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Drop files here to upload'] = 'Drop files to be uploaded here';
$a->strings['Cancel'] = 'Cancel';
$a->strings['Failed'] = 'Failed';
$a->strings['No files were uploaded.'] = 'No files were uploaded.';
$a->strings['Uploaded file is empty'] = 'Uploaded file is empty';
$a->strings['Upload was cancelled, or server error encountered'] = 'Upload was cancelled, or a server error was encountered';
