<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Fromapp settings updated."] = "Zaktualizowano ustawienia Fromapp.";
$a->strings["FromApp Settings"] = "Ustawienia FromApp";
$a->strings["The application name you would like to show your posts originating from."] = "Nazwa aplikacji, z której chcesz wyświetlać swoje posty.";
$a->strings["Use this application name even if another application was used."] = "Użyj tej nazwy aplikacji, nawet jeśli użyto innej aplikacji.";
$a->strings["Submit"] = "Zatwierdź";
