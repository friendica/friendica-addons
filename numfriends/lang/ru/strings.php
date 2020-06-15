<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Numfriends settings updated."] = "Настройки Numfriends обновлены.";
$a->strings["Numfriends Settings"] = "Настройки Numfriends";
$a->strings["How many contacts to display on profile sidebar"] = "Сколько контактов показывать на боковой панели профиля";
$a->strings["Submit"] = "Добавить";
