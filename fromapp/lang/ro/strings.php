<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Fromapp settings updated."] = "Configurările Fromapp au fost actualizate.";
$a->strings["FromApp Settings"] = "Configurări FromApp";
$a->strings["The application name you would like to show your posts originating from."] = "Denumirea aplicației pe care doriți să o afișați ca și origine pentru postările dvs.";
$a->strings["Use this application name even if another application was used."] = "Utilizați numele acestei aplicații chiar dacă o altă aplicație a fost utilizată.";
$a->strings["Submit"] = "Trimite";
