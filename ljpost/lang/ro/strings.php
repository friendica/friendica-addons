<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Post to LiveJournal"] = "Postați pe LiveJournal";
$a->strings["LiveJournal Post Settings"] = "Configurări Postări LiveJournal";
$a->strings["Enable LiveJournal Post Plugin"] = "Activare Modul Postare LiveJournal";
$a->strings["LiveJournal username"] = "Utilizator LiveJournal";
$a->strings["LiveJournal password"] = "Parolă LiveJournal ";
$a->strings["Post to LiveJournal by default"] = "Postați implicit pe LiveJournal";
$a->strings["Submit"] = "Trimite";
