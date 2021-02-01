<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Post to Dreamwidth"] = "Postați pe Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Configurări Postări Dreamwidth ";
$a->strings["Enable dreamwidth Post Addon"] = "Activare Modul Postare pe Dreamwidth";
$a->strings["dreamwidth username"] = "Utilizator Dreamwidth";
$a->strings["dreamwidth password"] = "Parola Dreamwidth ";
$a->strings["Post to dreamwidth by default"] = "Postați implicit pe Dreamwidth";
$a->strings["Submit"] = "Trimite";
