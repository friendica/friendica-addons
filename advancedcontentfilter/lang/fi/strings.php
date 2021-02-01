<?php

if(! function_exists("string_plural_select_fi")) {
function string_plural_select_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Add a Rule"] = "Lisää sääntö";
$a->strings["Your rules"] = "Sääntösi";
$a->strings["Disabled"] = "Ei käytössä";
$a->strings["Enabled"] = "Käytössä";
$a->strings["Enable this rule"] = "Ota tämä sääntö käyttöön";
$a->strings["Edit this rule"] = "Muokkaa tätä sääntöä";
$a->strings["Edit the rule"] = "Muokkaa sääntöä";
$a->strings["Save this rule"] = "Tallenna tämä sääntö";
$a->strings["Delete this rule"] = "Poista tämä sääntö";
$a->strings["Rule"] = "Sääntö";
$a->strings["Close"] = "Sulje";
$a->strings["Add new rule"] = "Lisää uusi sääntö";
$a->strings["Rule successfully added"] = "Sääntö lisätty";
$a->strings["Rule successfully updated"] = "Sääntö päivitetty";
$a->strings["Rule successfully deleted"] = "Sääntö poistettu";
