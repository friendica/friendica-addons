<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Smileybutton settings"] = "Configurări Smileybutton";
$a->strings["You can hide the button and show the smilies directly."] = "Puteți ascunde butonul şi afișa emoticoanele direct.";
$a->strings["Hide the button"] = "Ascundeţi butonul";
$a->strings["Save Settings"] = "Salvare Configurări";
