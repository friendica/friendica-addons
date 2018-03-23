<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["StatusNet AutoFollow settings updated."] = "Configurări StatusNet AutoFollow actualizate.";
$a->strings["StatusNet AutoFollow"] = "StatusNet AutoFollow";
$a->strings["Automatically follow any StatusNet followers/mentioners"] = "Urmărește automat orice susținător/pe cei ce vă menționează pe StatusNet";
$a->strings["Save Settings"] = "Salvare Configurări";
