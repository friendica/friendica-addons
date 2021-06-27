<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Select files for upload"] = "Fájlok kijelölése a feltöltéshez";
$a->strings["Drop files here to upload"] = "Húzzon ide fájlokat a feltöltéshez";
$a->strings["Cancel"] = "Mégse";
$a->strings["Failed"] = "Sikertelen";
$a->strings["No files were uploaded."] = "Nem lettek fájlok feltöltve.";
$a->strings["Uploaded file is empty"] = "A feltöltött fájl üres";
$a->strings["Image exceeds size limit of %s"] = "A kép meghaladja a beállított %s méretkorlátot";
$a->strings["File has an invalid extension, it should be one of %s."] = "A fájl érvénytelen kiterjesztéssel rendelkezik, %s egyikének kell lennie.";
$a->strings["Upload was cancelled, or server error encountered"] = "A feltöltés meg lett szakítva vagy kiszolgálóhiba történt";
