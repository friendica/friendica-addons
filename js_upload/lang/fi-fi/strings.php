<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Upload a file"] = "Lähetä tiedosto";
$a->strings["Cancel"] = "Peruuta";
$a->strings["Failed"] = "Epäonnistui";
$a->strings["No files were uploaded."] = "Tiedostoja ei lähetetty.";
$a->strings["Uploaded file is empty"] = "Lähetetty tiedosto on tyhjä";
$a->strings["Image exceeds size limit of "] = "Kuva ylittää kokorajoituksen ";
$a->strings["File has an invalid extension, it should be one of "] = "Tiedostopääte on virheellinen. Sallitut tiedostopäätteet:";
$a->strings["Upload was cancelled, or server error encountered"] = "Lataus peruutettu, tai palvelimessa tapahtui virhe.";
