<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Randplace Addon'] = 'A véletlen hely bővítmény engedélyezése';
$a->strings['Randplace Settings'] = 'Véletlen hely beállításai';
