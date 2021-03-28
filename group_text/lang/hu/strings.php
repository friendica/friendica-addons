<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Group Text"] = "Csoportszöveg";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Csak szöveges (kép nélküli) csoportválasztó használata a „csoportszerkesztés” menüben";
$a->strings["Save Settings"] = "Beállítások mentése";
