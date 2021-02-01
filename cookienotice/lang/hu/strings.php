<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["OK"] = "Rendben";
$a->strings["\"cookienotice\" Settings"] = "A sütifigyelmeztetés beállításai";
$a->strings["Cookie Usage Notice"] = "Sütihasználati figyelmeztetés";
$a->strings["The cookie usage notice"] = "A sütihasználati figyelmeztetés";
$a->strings["OK Button Text"] = "Rendben gomb szövege";
$a->strings["The OK Button text"] = "A „Rendben” gomb szövege";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["cookienotice Settings saved."] = "A sütifigyelmeztetés beállításai elmentve.";
