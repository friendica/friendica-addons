<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Gnot settings updated."] = "Paramètres de Gnot mis à jour.";
$a->strings["Gnot Settings"] = "Paramètres Gnot";
