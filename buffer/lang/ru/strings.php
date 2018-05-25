<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Permission denied."] = "Доступ запрещен.";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Client ID"] = "Client ID";
$a->strings["Client Secret"] = "Client Secret";
$a->strings["Error when registering buffer connection:"] = "Ошибка при регистрации соединения Buffer:";
$a->strings["You are now authenticated to buffer. "] = "Вы аутентифицированы на Buffer.";
$a->strings["return to the connector page"] = "вернуться на страницу коннектора";
$a->strings["Post to Buffer"] = "Написать в Buffer";
$a->strings["Buffer Export"] = "Экспорт в Buffer";
$a->strings["Authenticate your Buffer connection"] = "Аутентифицируйте свое соединение с Buffer";
$a->strings["Enable Buffer Post Addon"] = "Включить аддон Buffer Post";
$a->strings["Post to Buffer by default"] = "Отправлять в Buffer по умолчанию";
$a->strings["Check to delete this preset"] = "Отметьте для удаления этих настроек";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Сообщения уходят во все учетные записи по умолчанию:";
