<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Post to Dreamwidth"] = "Отправить в Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Настройки сообщений Dreamwidth";
$a->strings["Enable dreamwidth Post Addon"] = "Включить аддон dreamwidth Post";
$a->strings["dreamwidth username"] = "Имя пользователя Dreamwidth";
$a->strings["dreamwidth password"] = "Пароль Dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Отправлять сообщения в Dreamwidth по умолчанию";
$a->strings["Submit"] = "Добавить";
