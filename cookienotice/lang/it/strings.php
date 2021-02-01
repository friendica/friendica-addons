<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["OK"] = "OK";
$a->strings["\"cookienotice\" Settings"] = "Impostazioni \"cookienotice\"";
$a->strings["Cookie Usage Notice"] = "Nota Utilizzo Cookie";
$a->strings["The cookie usage notice"] = "La nota di utilizzo dei cookie";
$a->strings["OK Button Text"] = "Testo Bottone OK";
$a->strings["The OK Button text"] = "Il testo del bottone OK";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["cookienotice Settings saved."] = "Impostazioni \"cookienotice\" salvate.";
