<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Gnot settings updated."] = "Настройки Gnot обновлены.";
$a->strings["Gnot Settings"] = "Настройки Gnot";
$a->strings["Allows threading of email comment notifications on Gmail and anonymising the subject line."] = "Разрешить нитевание уведомлений о комментариях на Gmail и анонимизировать поле \"Тема\".";
$a->strings["Enable this addon?"] = "Включить этот плагин/аддон?";
$a->strings["Submit"] = "Добавить";
$a->strings["[Friendica:Notify] Comment to conversation #%d"] = "[Friendica:Notify] Комментарий в теме #%d";
