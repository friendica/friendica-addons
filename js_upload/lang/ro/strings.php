<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Upload a file"] = "Încărcați un fișier";
$a->strings["Drop files here to upload"] = "Fixați fișierele aici pentru încărcare";
$a->strings["Cancel"] = "Anulare";
$a->strings["Failed"] = "Eșuat";
$a->strings["No files were uploaded."] = "Nici un fișier nu a fost încărcat.";
$a->strings["Uploaded file is empty"] = "Fișierul încărcat este gol";
$a->strings["Image exceeds size limit of "] = "Dimensiunea imaginii depășește limita de";
$a->strings["File has an invalid extension, it should be one of "] = "Fișierul are o extensie invalidă, acesta trebuie să fie una de tipul";
$a->strings["Upload was cancelled, or server error encountered"] = "Încărcarea a fost anulată, sau a apărut o eroare de server";
