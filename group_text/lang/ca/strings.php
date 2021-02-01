<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Group Text settings updated."] = "La configuració del text del grup s'ha actualitzat.";
$a->strings["Group Text"] = "Missatge del grup";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Utilitzeu un selector de grup de només text (que no sigui una imatge) al menú 'Edita grup'";
$a->strings["Submit"] = "sotmetre's";
