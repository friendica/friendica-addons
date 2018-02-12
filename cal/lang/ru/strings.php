<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Event Export"] = "Экспорт событий";
$a->strings["You can download public events from: "] = "Вы можете скачать публичные события из:";
$a->strings["The user does not export the calendar."] = "Пользователь не экспортировал календарь.";
$a->strings["This calendar format is not supported"] = "Этот формат календарей не поддерживается";
$a->strings["Export Events"] = "Экспорт событий";
$a->strings["If this is enabled, your public events will be available at"] = "Если эта настройка включена, то ваши публичные события будут доступны на:";
$a->strings["Currently supported formats are ical and csv."] = "Текущие поддерживаемые форматы ical и csv.";
$a->strings["Enable calendar export"] = "Включить экспорт календаря";
$a->strings["Save Settings"] = "Сохранить настройки";
