<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Group Text settings updated."] = "Nastavení Group Text aktualizována.";
$a->strings["Group Text"] = "Skupinový text";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Použijte pouze textový (bezobrázkový) výběr skupiny v menu úpravy skupin.";
$a->strings["Submit"] = "Odeslat";
