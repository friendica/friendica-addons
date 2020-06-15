<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Blockem"] = "Blockem";
$a->strings["Hides user's content by collapsing posts. Also replaces their avatar with generic image."] = "Скрыть контент пользователя. Также заменяет его аватар изображением по-умолчанию.";
$a->strings["Comma separated profile URLS:"] = "URL профилей, разделенные запятыми:";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["BLOCKEM Settings saved."] = "BLOCKEM Настройки сохранены.";
$a->strings["Filtered user: %s"] = "Отфильтрованный пользователь: %s";
$a->strings["Unblock Author"] = "Разблокировать автора";
$a->strings["Block Author"] = "Блокировать автора";
$a->strings["blockem settings updated"] = "Настройки Blockem обновлены";
