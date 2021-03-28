<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Post to LiveJournal"] = "Отправить в LiveJournal";
$a->strings["LiveJournal Post Settings"] = "Настройки отправки в LiveJournal";
$a->strings["Enable LiveJournal Post Addon"] = "Включить отправку в LiveJournal";
$a->strings["LiveJournal username"] = "Имя пользователя LiveJournal";
$a->strings["LiveJournal password"] = "Пароль в LiveJournal";
$a->strings["Post to LiveJournal by default"] = "Отправлять записи в LiveJournal по-умолчанию";
$a->strings["Save Settings"] = "Сохранить настройки";
