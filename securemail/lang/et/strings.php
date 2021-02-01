<?php

if(! function_exists("string_plural_select_et")) {
function string_plural_select_et($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"Secure Mail\" Settings"] = "\"Turvaline Meil\" sätted";
$a->strings["Save Settings"] = "Salvesta sätted";
$a->strings["Save and send test"] = "Salvesta ja saada testmeil";
$a->strings["Enable Secure Mail"] = "Aktiveeri Turvaline meil";
$a->strings["Public key"] = "Avalik võti";
$a->strings["Secure Mail Settings saved."] = "Turvalise Meili sätted salvestatud.";
$a->strings["Test email sent"] = "Testmeil saadetud";
$a->strings["There was an error sending the test email"] = "Testmeili saatmisel ilmnes viga";
