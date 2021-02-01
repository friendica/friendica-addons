<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["WindowsPhonePush settings updated."] = "Configurări  WindowsPhonePush actualizate.";
$a->strings["WindowsPhonePush Settings"] = "Configurare  WindowsPhonePush";
$a->strings["Enable WindowsPhonePush Addon"] = "Activare Addon WindowsPhonePush";
$a->strings["Push text of new item"] = "Tastează textul noului element";
$a->strings["Save Settings"] = "Salvare Configurări";
