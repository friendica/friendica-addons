<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["\"pageheader\" Settings"] = "\"pageheader\" Ustawienia";
$a->strings["Message"] = "Wiadomość";
$a->strings["Message to display on every page on this server (or put a pageheader.html file in your docroot)"] = "Wiadomość do wyświetlenia na każdej stronie tego serwera (lub umieść plik pageheader.html w swoim dokumencie roboczym)";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["pageheader Settings saved."] = "pageheader Ustawienia zapisane.";
