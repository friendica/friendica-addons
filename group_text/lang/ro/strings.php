<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Group Text settings updated."] = "Configurările Text Grup, au fost actualizate.";
$a->strings["Group Text"] = "Text Grup";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Folosiți în meniul \"editare grup\" un selector de grup strict textual (fără-imagine)";
$a->strings["Submit"] = "Trimite";
