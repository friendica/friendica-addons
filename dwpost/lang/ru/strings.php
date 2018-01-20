<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Post to Dreamwidth"] = "Отправить в Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Настройки сообщений Dreamwidth";
$a->strings["Enable dreamwidth Post Addon"] = "Включить плагин отправки сообщений в Dreamwidth";
$a->strings["dreamwidth username"] = "Имя пользователя Dreamwidth";
$a->strings["dreamwidth password"] = "Пароль Dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Отправлять сообщения в Dreamwidth по умолчанию";
$a->strings["Submit"] = "Добавить";
