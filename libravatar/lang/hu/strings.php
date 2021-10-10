<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['generic profile image'] = 'Általános profilkép';
$a->strings['random geometric pattern'] = 'Véletlen geometriai minta';
$a->strings['monster face'] = 'Szörnyarc';
$a->strings['computer generated face'] = 'Számítógéppel előállított arc';
$a->strings['retro arcade style face'] = 'Retró árkádstílusú arc';
$a->strings['roboter face'] = 'Robotarc';
$a->strings['retro adventure game character'] = 'Retró kalandjáték-karakter';
$a->strings['Information'] = 'Információ';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'A Gravatar bővítmény telepítve van. Tiltsa le a Gravatar bővítményt.<br>A Libravatar bővítmény vissza fog állni a Gravatarra, ha semmi sem található a Libravatarnál.';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Default avatar image'] = 'Alapértelmezett profilkép';
$a->strings['Select default avatar image if none was found. See README'] = 'Az alapértelmezett profilkép kiválasztása, ha semmi sem található. Nézze meg a README információkat.';
