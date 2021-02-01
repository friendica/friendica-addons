<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"Show more\" Settings"] = "Impostazioni \"Mostra altro\"";
$a->strings["Enable Show More"] = "Abilita \"Mostra altro\"";
$a->strings["Cutting posts after how much characters"] = "Dopo quanti caratteri tagliare il messaggio";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Show More Settings saved."] = "Impostazioni \"Mostra altro\" salvate.";
$a->strings["show more"] = "mostra di più";
