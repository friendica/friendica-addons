<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Planets Settings"] = "Planets Nastavení";
$a->strings["Enable Planets Plugin"] = "Povolit Planets plugin";
$a->strings["Submit"] = "Odeslat";
