<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Geonames Settings'] = 'Földrajzi nevek beállításai';
$a->strings['Replace numerical coordinates by the nearest populated location name in your posts.'] = 'Számokkal megadott koordináták cseréje a bejegyzéseiben a legközelebbi lakott hely nevére.';
$a->strings['Enable Geonames Addon'] = 'A földrajzi nevek bővítmény engedélyezése';
$a->strings['Save Settings'] = 'Beállítások mentése';
