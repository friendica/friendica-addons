<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Post to Google+"] = "Postați pe Google+";
$a->strings["Enable Google+ Post Plugin"] = "Activare Modul Postare Google+";
$a->strings["Google+ username"] = "Utilizator Google+ ";
$a->strings["Google+ password"] = "Parola Google+";
$a->strings["Google+ page number"] = "Numărul paginii Google+ ";
$a->strings["Post to Google+ by default"] = "Postați implicit pe Google+";
$a->strings["Do not prevent posting loops"] = "Nu se previn înlănțuirile postării";
$a->strings["Skip messages without links"] = "Se omit mesajele fără legături";
$a->strings["Mirror all public posts"] = "Reproducere pentru toate postările publice";
$a->strings["Mirror Google Account ID"] = "Reproducere ID Cont Google";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Google+ post failed. Queued for retry."] = "Postarea pe Google+ a eșuat. S-a pus în așteptare pentru reîncercare.";
