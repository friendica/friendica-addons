<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Login"] = "Вход";
$a->strings["OpenID"] = "OpenID";
$a->strings["Latest users"] = "Последние пользователи";
$a->strings["Most active users"] = "Самые активные пользователи";
$a->strings["Latest photos"] = "Последние фото";
$a->strings["Contact Photos"] = "Фотографии контакта";
$a->strings["Profile Photos"] = "Фотографии профиля";
$a->strings["Latest likes"] = "Последние отметки \"нравится\"";
$a->strings["event"] = "событие";
$a->strings["status"] = "статус";
$a->strings["photo"] = "фото";
$a->strings["%1\$s likes %2\$s's %3\$s"] = "%1\$s нравится %3\$s от %2\$s ";
$a->strings["Welcome to %s"] = "Добро пожаловать на %s!";
