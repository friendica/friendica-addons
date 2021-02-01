<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Dreamwidth"] = "Publica a Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Configuració de la publicació de Dreamwidth";
$a->strings["Enable dreamwidth Post Addon"] = "Habilita Addon Post Post";
$a->strings["dreamwidth username"] = "nom d'usuari de dreamwidth";
$a->strings["dreamwidth password"] = "contrasenya de dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Publica l'amplada de somni de manera predeterminada";
$a->strings["Submit"] = "sotmetre's";
