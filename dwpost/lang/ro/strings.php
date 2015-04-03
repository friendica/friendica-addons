<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Post to Dreamwidth"] = "Postați pe Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Configurări Postări Dreamwidth ";
$a->strings["Enable dreamwidth Post Plugin"] = "Activare Modul Postare pe Dreamwidth";
$a->strings["dreamwidth username"] = "Utilizator Dreamwidth";
$a->strings["dreamwidth password"] = "Parola Dreamwidth ";
$a->strings["Post to dreamwidth by default"] = "Postați implicit pe Dreamwidth";
$a->strings["Submit"] = "Trimite";
