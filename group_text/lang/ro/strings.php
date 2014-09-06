<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Group Text settings updated."] = "Configurările Text Grup, au fost actualizate.";
$a->strings["Group Text"] = "Text Grup";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Folosiți în meniul \"editare grup\" un selector de grup strict textual (fără-imagine)";
$a->strings["Save Settings"] = "Salvare Configurări";
