<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Content Filter (NSFW and more)"] = "Sisällönsuodatin (NSFW yms.)";
$a->strings["Enable Content filter"] = "Ota sisällönsuodatin käyttöön";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["NSFW Settings saved."] = "NSFW-asetukset tallennettu.";
$a->strings["Filtered tag: %s"] = "Suodatettu tunniste: %s";
$a->strings["Filtered word: %s"] = "Suodatettu sana: %s";
