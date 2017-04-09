<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Post to Google+"] = "Написать в Google+";
$a->strings["Enable Google+ Post Plugin"] = "Включить плагин Google+ Post";
$a->strings["Google+ username"] = "Имя пользователя Google+";
$a->strings["Google+ password"] = "Пароль Google+";
$a->strings["Google+ page number"] = "Номер страницы Google+";
$a->strings["Post to Google+ by default"] = "Отправлять в Google+ по умолчанию";
$a->strings["Do not prevent posting loops"] = "Не предотвращать петли отправки";
$a->strings["Skip messages without links"] = "Пропускать сообщения без ссылок";
$a->strings["Mirror all public posts"] = "Зеркалировать все публичные сообщения";
$a->strings["Mirror Google Account ID"] = "Зеркалировать Google Account ID";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Google+ post failed. Queued for retry."] = "Ошибка отправки сообщения в Google+. В очереди на еще одну попытку.";
