<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Dreamwidth"] = "Invia a Dreamwidth";
$a->strings["Dreamwidth Export"] = "Esporta Dreamwidth";
$a->strings["Enable dreamwidth Post Addon"] = "Abilita il componente aggiuntivo di invio a Dreamwidth";
$a->strings["dreamwidth username"] = "Nome utente Dreamwidth";
$a->strings["dreamwidth password"] = "password Dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Invia sempre a Dreamwidth";
$a->strings["Save Settings"] = "Salva Impostazioni";
