<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Permission denied."] = "Доступ запрещен.";
$a->strings["You are now authenticated to app.net. "] = "Вы аутентифицированы на app.net";
$a->strings["<p>Error fetching token. Please try again.</p>"] = "<p>Ошибка получения токена. Попробуйте еще раз.</p>";
$a->strings["return to the connector page"] = "вернуться на страницу коннектора";
$a->strings["Post to app.net"] = "Отправить на app.net";
$a->strings["App.net Export"] = "Экспорт app.net";
$a->strings["Currently connected to: "] = "В настоящее время соединены с: ";
$a->strings["Enable App.net Post Plugin"] = "Включить плагин App.net";
$a->strings["Post to App.net by default"] = "Отправлять сообщения на App.net по-умолчанию";
$a->strings["Import the remote timeline"] = "Импортировать удаленные сообщения";
$a->strings["<p>Error fetching user profile. Please clear the configuration and try again.</p>"] = "<p>Ошибка при получении профиля пользователя. Сбросьте конфигурацию и попробуйте еще раз.</p>";
$a->strings["<p>You have two ways to connect to App.net.</p>"] = "<p>У вас есть два способа соединения с App.net.</p>";
$a->strings["<p>First way: Register an application at <a href=\"https://account.app.net/developer/apps/\">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. "] = "<p>Первый способ: зарегистрируйте приложение на <a href=\"https://account.app.net/developer/apps/\">https://account.app.net/developer/apps/</a> и введите Client ID и Client Secret";
$a->strings["Use '%s' as Redirect URI<p>"] = "Используйте '%s' как Redirect URI<p>";
$a->strings["Client ID"] = "Client ID";
$a->strings["Client Secret"] = "Client Secret";
$a->strings["<p>Second way: fetch a token at <a href=\"http://dev-lite.jonathonduerig.com/\">http://dev-lite.jonathonduerig.com/</a>. "] = "<p>Второй путь: получите токен на <a href=\"http://dev-lite.jonathonduerig.com/\">http://dev-lite.jonathonduerig.com/</a>. ";
$a->strings["Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>"] = "Выберите области: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>";
$a->strings["Token"] = "Токен";
$a->strings["Sign in using App.net"] = "Войти через App.net";
$a->strings["Clear OAuth configuration"] = "Удалить конфигурацию OAuth";
$a->strings["Save Settings"] = "Сохранить настройки";
