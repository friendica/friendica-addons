<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Show callstack'] = 'Hívásverem megjelenítése';
$a->strings['Show detailed performance measures in the callstack. When deactivated, only the summary will be displayed.'] = 'Részletes teljesítménymérések megjelenítése a hívásveremben. Ha ki van kapcsolva, akkor csak az összegzés lesz megjelenítve.';
$a->strings['Minimal time'] = 'Legkevesebb idő';
$a->strings['Minimal time that an activity needs to be listed in the callstack.'] = 'A legkevesebb idő, amíg egy tevékenységnek szerepelnie kell a hívásveremben.';
$a->strings['Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s'] = 'Adatbázis: %s/%s, hálózat: %s, megjelenítés: %s, munkamenet: %s, lemezművelet: %s, egyéb: %s, összesen: %s';
$a->strings['Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s'] = 'Osztály-előkészítés: %s, indítás: %s, előkészítés: %s, tartalom: %s, egyéb: %s, összesen: %s';
