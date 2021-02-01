<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
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
