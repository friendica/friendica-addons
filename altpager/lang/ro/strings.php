<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Altpager settings updated."] = "Configurările Altpager au fost actualizate.";
$a->strings["Alternate Pagination Setting"] = "Configurare Paginație Alternantă";
$a->strings["Use links to \"newer\" and \"older\" pages in place of page numbers?"] = "Se utilizează legături pentru paginile \"mai noi\" şi \"mai vechi\", în locul de numerelor de pagină?";
$a->strings["Submit"] = "Trimite";
$a->strings["Global"] = "Global";
$a->strings["Force global use of the alternate pager"] = "Se forțează utilizarea globală a paginatorului alternant";
$a->strings["Individual"] = "Individual";
$a->strings["Each user chooses whether to use the alternate pager"] = "Fiecare utilizator alege dacă va utiliza paginatorul alternant";
$a->strings["Settings updated."] = "Configurări actualizate.";
