<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Planets Settings"] = "Configurări Planets";
$a->strings["Enable Planets Addon"] = "Activare Modul Planets";
$a->strings["Submit"] = "Trimite";
