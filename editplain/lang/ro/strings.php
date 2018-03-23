<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Editplain settings updated."] = "Configurările Editplain au fost actualizate.";
$a->strings["Editplain Settings"] = "Configurări Editplain";
$a->strings["Disable richtext status editor"] = "Dezactivare editorul status de text îmbogățit";
$a->strings["Submit"] = "Trimite";
