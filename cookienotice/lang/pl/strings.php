<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["\"cookienotice\" Settings"] = "";
$a->strings["<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button."] = "";
$a->strings["Cookie Usage Notice"] = "";
$a->strings["The cookie usage notice"] = "";
$a->strings["OK Button Text"] = "Tekst przycisku OK";
$a->strings["The OK Button text"] = "";
$a->strings["Save Settings"] = "";
$a->strings["cookienotice Settings saved."] = "";
