<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s'] = 'Adatbázis: %s/%s, hálózat: %s, megjelenítés: %s, munkamenet: %s, lemezművelet: %s, egyéb: %s, összesen: %s';
$a->strings['Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s'] = 'Osztály-előkészítés: %s, indítás: %s, előkészítés: %s, tartalom: %s, egyéb: %s, összesen: %s';
