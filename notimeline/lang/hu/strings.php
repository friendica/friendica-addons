<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["No Timeline Settings"] = "Nincs idővonal beállításai";
$a->strings["Disable Archive selector on profile wall"] = "Archiválás kiválasztó letiltása a profil falán";
$a->strings["Save Settings"] = "Beállítások mentése";
