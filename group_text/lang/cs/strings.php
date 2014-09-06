<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Group Text settings updated."] = "Nastavení textu skupiny aktualizováno.";
$a->strings["Group Text"] = "Skupinový text";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Použijte pouze textový (neobrázkový) výběr skupiny v menu editace skupin.";
$a->strings["Save Settings"] = "Uložit Nastavení";
