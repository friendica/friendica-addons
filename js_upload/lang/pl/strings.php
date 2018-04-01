<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Upload a file"] = "Prześlij plik";
$a->strings["Drop files here to upload"] = "Upuść pliki tutaj, aby przesłać";
$a->strings["Cancel"] = "Anuluj";
$a->strings["Failed"] = "Nie powiodło się";
$a->strings["No files were uploaded."] = "Żadne pliki nie zostały przesłane.";
$a->strings["Uploaded file is empty"] = "Przesłany plik jest pusty";
$a->strings["Image exceeds size limit of "] = "Obraz przekracza limit rozmiaru wynoszący";
$a->strings["File has an invalid extension, it should be one of "] = "Plik ma nieprawidłowe rozszerzenie, powinno być jednym z nich";
$a->strings["Upload was cancelled, or server error encountered"] = "Przesyłanie zostało anulowane lub wystąpił błąd serwera";
