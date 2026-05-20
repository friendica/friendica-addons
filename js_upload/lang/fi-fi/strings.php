<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Cancel'] = 'Peruuta';
$a->strings['Failed'] = 'Epäonnistui';
$a->strings['No files were uploaded.'] = 'Tiedostoja ei lähetetty.';
$a->strings['Uploaded file is empty'] = 'Lähetetty tiedosto on tyhjä';
$a->strings['Upload was cancelled, or server error encountered'] = 'Lataus peruutettu, tai palvelimessa tapahtui virhe.';
