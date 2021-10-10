<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Fromapp settings updated.'] = 'A FromApp beállításai frissítve.';
$a->strings['FromApp Settings'] = 'FromApp-beállítások';
$a->strings['The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'] = 'Az alkalmazás neve, amelyet meg szeretne jeleníteni a bejegyzései származási helyeként. A különböző alkalmazásnevek vesszővel választhatók el. Ezután véletlenszerűen lesz kiválasztva az egyikük minden egyes beküldésnél.';
$a->strings['Use this application name even if another application was used.'] = 'Ezen alkalmazásnév használata akkor is, ha egy másik alkalmazás lett használva.';
$a->strings['Save Settings'] = 'Beállítások mentése';
