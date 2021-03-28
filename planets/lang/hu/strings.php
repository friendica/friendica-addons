<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Planets"] = "Bolygók";
$a->strings["Planets Settings"] = "Bolygók beállításai";
$a->strings["Enable Planets Addon"] = "Bolygók bővítmény engedélyezése";
$a->strings["Save Settings"] = "Beállítások mentése";
