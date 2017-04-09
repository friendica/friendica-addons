<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Forums"] = "Форумы";
$a->strings["show/hide"] = "показать/скрыть";
$a->strings["No forum subscriptions"] = "Нет подписок на форумы";
$a->strings["Forums:"] = "Форумы:";
$a->strings["Forumlist settings updated."] = "Настройки Forumlist обновлены.";
$a->strings["Forumlist Settings"] = "Настройки Forumlist";
$a->strings["Randomise forum list"] = "Случайный список форумов";
$a->strings["Show forums on profile page"] = "Показывать форумы на странице профиля";
$a->strings["Show forums on network page"] = "Показывать форумы на странице сети";
$a->strings["Submit"] = "Добавить";
