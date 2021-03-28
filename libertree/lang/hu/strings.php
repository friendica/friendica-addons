<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to libertree"] = "Beküldés a Libertree-re";
$a->strings["libertree Export"] = "Libertree exportálás";
$a->strings["Enable Libertree Post Addon"] = "A Libertree-beküldő bővítmény engedélyezése";
$a->strings["Libertree API token"] = "Libertree API token";
$a->strings["Libertree site URL"] = "Libertree oldal URL";
$a->strings["Post to Libertree by default"] = "Beküldés a Libertree-re alapértelmezetten";
$a->strings["Save Settings"] = "Beállítások mentése";
