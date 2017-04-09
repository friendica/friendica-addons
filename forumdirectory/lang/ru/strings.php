<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Forum Directory"] = "Каталог форумов";
$a->strings["Public access denied."] = "Свободный доступ закрыт.";
$a->strings["Global Directory"] = "Глобальный каталог";
$a->strings["Find on this site"] = "Найти на этом сайте";
$a->strings["Finding: "] = "Результат поиска: ";
$a->strings["Site Directory"] = "Каталог сайта";
$a->strings["Find"] = "Найти";
$a->strings["Age: "] = "Возраст: ";
$a->strings["Gender: "] = "Пол: ";
$a->strings["Location:"] = "Откуда:";
$a->strings["Gender:"] = "Пол:";
$a->strings["Status:"] = "Статус:";
$a->strings["Homepage:"] = "Домашняя страничка:";
$a->strings["About:"] = "О себе:";
$a->strings["No entries (some entries may be hidden)."] = "Нет записей (некоторые записи могут быть скрыты).";
