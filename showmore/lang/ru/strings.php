<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["\"Show more\" Settings"] = "Настройки \"показать ещё\"";
$a->strings["Enable Show More"] = "Включить \"показать ещё\"";
$a->strings["Cutting posts after how much characters"] = "Обрезать записи после превышения этого числа символов";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Show More Settings saved."] = "Настройки сохранены.";
$a->strings["show more"] = "показать ещё";
