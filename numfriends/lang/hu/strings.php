<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['How many contacts to display on profile sidebar'] = 'Hány partner legyen megjelenítve a profil oldalsávján';
$a->strings['Numfriends Settings'] = 'Ismerősszám beállításai';
