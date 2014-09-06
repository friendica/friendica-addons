<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Post to app.net"] = "Postați pe App.net";
$a->strings["App.net Export"] = "Exportare pe App.net";
$a->strings["Enable App.net Post Plugin"] = "Activare Modul Postare pe App.net";
$a->strings["Post to App.net by default"] = "Postați implicit pe App.net";
$a->strings["Save Settings"] = "Salvare Configurări";
