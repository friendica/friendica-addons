<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["No Timeline settings updated."] = "Configurările pentru Lipsă Cronologie, au fost actualizate.";
$a->strings["No Timeline Settings"] = "Configurări pentru Lipsă Cronologie";
$a->strings["Disable Archive selector on profile wall"] = "Dezactivare selector Arhivă din peretele de profil";
$a->strings["Submit"] = "Trimite";
