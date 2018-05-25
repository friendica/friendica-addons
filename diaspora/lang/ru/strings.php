<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Post to Diaspora"] = "Отправить в Diaspora";
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = "Невозможно войти в вашу учетную запись Diaspora. Пожалуйста, проверьте имя пользователя, пароль и убедитесь, что вы ввели полный адрес пода (включая http/https)";
$a->strings["Diaspora Export"] = "Экспорт в Diaspora";
$a->strings["Enable Diaspora Post Addon"] = "Включить плагин отправки сообщений в Diaspora";
$a->strings["Diaspora username"] = "Имя пользователя Diaspora";
$a->strings["Diaspora password"] = "Пароль Diaspora";
$a->strings["Diaspora site URL"] = "URL пода Diaspora";
$a->strings["Post to Diaspora by default"] = "Отправлять в Diaspora по умолчанию";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Diaspora post failed. Queued for retry."] = "Ошибка отправки сообщения в Diaspora. В очереди на еще одну попытку.";
