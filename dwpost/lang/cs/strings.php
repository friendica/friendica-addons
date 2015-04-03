<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Post to Dreamwidth"] = "Poslat na Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Dreamwidth nastavení příspěvků";
$a->strings["Enable dreamwidth Post Plugin"] = "Povolit dreamwidth Plugin";
$a->strings["dreamwidth username"] = "dreamwidth uživatelské jméno";
$a->strings["dreamwidth password"] = "dreamwidth heslo";
$a->strings["Post to dreamwidth by default"] = "Defaultně umístit na dreamwidth";
$a->strings["Submit"] = "Odeslat";
