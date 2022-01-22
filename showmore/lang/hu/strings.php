<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Show More'] = 'Több megjelenítésének engedélyezése';
$a->strings['Cutting posts after how many characters'] = 'Bejegyzések levágása ennyi karakter után';
$a->strings['"Show more" Settings'] = '„Több megjelenítése” beállításai';
$a->strings['show more'] = 'több megjelenítése';
