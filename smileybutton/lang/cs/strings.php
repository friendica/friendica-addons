<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n==1)) { return 0; } else if (($n>=2 && $n<=4)) { return 1; } else  { return 2; }
}}
;
$a->strings["Smileybutton settings"] = "Smileybutton nastavení";
$a->strings["You can hide the button and show the smilies directly."] = "Můžete skrýt tlačítko a zobrazit rovnou smajlíky.";
$a->strings["Hide the button"] = "Skrýt tlačítko";
$a->strings["Save Settings"] = "Uložit Nastavení";
