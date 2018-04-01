<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Group Text settings updated."] = "Zaktualizowano ustawienia tekstu grupowego.";
$a->strings["Group Text"] = "Grupuj tekst";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Użyj selektora grupy tylko tekst (inny niż obraz) w menu \"Edycja grupy\"";
$a->strings["Submit"] = "Zatwierdź";
