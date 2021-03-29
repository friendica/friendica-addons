<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["generic profile image"] = "Általános profilkép";
$a->strings["random geometric pattern"] = "Véletlen geometriai minta";
$a->strings["monster face"] = "Szörnyarc";
$a->strings["computer generated face"] = "Számítógéppel előállított arc";
$a->strings["retro arcade style face"] = "Retró árkádstílusú arc";
$a->strings["Information"] = "Információ";
$a->strings["Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar."] = "A Libravatar bővítmény is telepítve van. Tiltsa le a Libravatar bővítményt vagy ezt a Gravatar bővítményt.<br>A Libravatar bővítmény vissza fog állni a Gravatarra, ha semmi sem található a Libravatarnál.";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Default avatar image"] = "Alapértelmezett profilkép";
$a->strings["Select default avatar image if none was found at Gravatar. See README"] = "Az alapértelmezett profilkép kiválasztása, ha semmi sem található a Gravatarnál. Nézze meg a README információkat.";
$a->strings["Rating of images"] = "Képek értékelése";
$a->strings["Select the appropriate avatar rating for your site. See README"] = "A megfelelő profilkép-értékelés kiválasztása az oldalához. Nézze meg a README információkat.";
