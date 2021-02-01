<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Dreamwidth"] = "Beküldés a Dreamwidth-re";
$a->strings["Dreamwidth Post Settings"] = "Dreamwidth-beküldés beállításai";
$a->strings["Enable dreamwidth Post Addon"] = "A Dreamwidth-beküldő bővítmény engedélyezése";
$a->strings["dreamwidth username"] = "Dreamwidth felhasználónév";
$a->strings["dreamwidth password"] = "Dreamwidth jelszó";
$a->strings["Post to dreamwidth by default"] = "Beküldés a Dreamwidth-re alapértelmezetten";
$a->strings["Submit"] = "Elküldés";
