<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Startpage"] = "Kezdőlap";
$a->strings["Home page to load after login  - leave blank for profile wall"] = "Betöltendő kezdőoldal bejelentkezés után - hagyja üresen a profilfalhoz";
$a->strings["Examples: &quot;network&quot; or &quot;notifications/system&quot;"] = "Példák: „network” vagy „notifications/system”";
$a->strings["Save Settings"] = "Beállítások mentése";
