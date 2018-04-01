<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Smileybutton settings"] = "Ustawienia Smileybutton";
$a->strings["You can hide the button and show the smilies directly."] = "Możesz ukryć przycisk i bezpośrednio pokazać emotikony.";
$a->strings["Hide the button"] = "Ukryj przycisk";
$a->strings["Save Settings"] = "Zapisz ustawienia";
