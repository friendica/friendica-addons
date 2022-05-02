<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Select files for upload'] = 'Vælg filer til upload';
$a->strings['Drop files here to upload'] = 'Drop filer her til upload';
$a->strings['Cancel'] = 'Annullér';
$a->strings['Failed'] = 'Fejlede';
$a->strings['No files were uploaded.'] = 'Ingen filer blev uploadet.';
$a->strings['Uploaded file is empty'] = 'Uploadede fil er tom';
$a->strings['Image exceeds size limit of %s'] = 'Billede overskrider størrelsesbegrænsning på %s';
$a->strings['File has an invalid extension, it should be one of %s.'] = 'Fil har en ugyldig udvidelse, det skal være en af %s.';
$a->strings['Upload was cancelled, or server error encountered'] = 'Upload blev annulleret, eller der skete en fejl på serveren';
