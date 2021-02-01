<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["\"Superblock\""] = "\"Superblock\"";
$a->strings["Comma separated profile URLS to block"] = "Estettävien profiilien URL-osoitteet pilkulla erotettuina";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["SUPERBLOCK Settings saved."] = "Superblock -asetukset tallennettu.";
$a->strings["Block Completely"] = "Estä kokonaan";
$a->strings["superblock settings updated"] = "superblock -asetukset päivitetty";
