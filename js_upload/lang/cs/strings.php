<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n==1)) { return 0; } else if (($n>=2 && $n<=4)) { return 1; } else  { return 2; }
}}
;
$a->strings["Upload a file"] = "Nahrát soubor";
$a->strings["Drop files here to upload"] = "Přeneste sem soubory k nahrání";
$a->strings["Cancel"] = "Zrušit";
$a->strings["Failed"] = "Neúspěch";
$a->strings["No files were uploaded."] = "Žádné soubory nebyly nahrány.";
$a->strings["Uploaded file is empty"] = "Nahraný soubor je prázdný";
$a->strings["Image exceeds size limit of "] = "Velikost obrázku překračuje limit velikosti";
$a->strings["File has an invalid extension, it should be one of "] = "Soubor má neplatnou příponu, ta by měla být jednou z";
$a->strings["Upload was cancelled, or server error encountered"] = "Nahrávání bylo zrušeno nebo došlo k chybě na serveru";
