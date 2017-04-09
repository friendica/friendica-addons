<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["YourLS Settings"] = "Настройки YourLS";
$a->strings["URL: http://"] = "URL: http://";
$a->strings["Username:"] = "Имя пользователя:";
$a->strings["Password:"] = "Пароль:";
$a->strings["Use SSL "] = "Использовать SSL";
$a->strings["Submit"] = "Добавить";
$a->strings["yourls Settings saved."] = "Настройки YourLS сохранены.";
