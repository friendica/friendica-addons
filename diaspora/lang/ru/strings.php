<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Post to Diaspora"] = "Отправить в Diaspora";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "Помните: с вами всегда можно связаться из сети Diaspora по вашему адресу Friendica <strong>%s</strong>.";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Этот коннектор предназначен только для тех случаев, когда вы хотите использовать вашу старую учётную запись в Diaspora в течение какого-то времени.";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "Но будет лучше, если вы просто предложите вашим контактам в Diaspora использовать ваш новый адрес <strong>%s</strong>.";
$a->strings["All aspects"] = "Все контакты";
$a->strings["Public"] = "Публично";
$a->strings["Post to aspect:"] = "Для группы:";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Соединено с вашей учётной записью Diaspora <strong>%s</strong>";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "Не получается зайти в вашу учётную запись Diaspora. Пожалуйста, проверьте правильность имени (в формате user@domain.tld) и пароль.";
$a->strings["Diaspora Export"] = "Экспорт в Diaspora";
$a->strings["Information"] = "Информация";
$a->strings["Error"] = "Ошибка";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Enable Diaspora Post Addon"] = "Включить аддон Diaspora Post";
$a->strings["Diaspora handle"] = "Имя в Diaspora";
$a->strings["Diaspora password"] = "Пароль Diaspora";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "Предупреждение: Ваш пароль для Diaspora будет сохранён в открытом виде, чтобы производить вход в ваш сервер Diaspora. Это означает, что администратор этого узла Friendica может получить к нему доступ.";
$a->strings["Post to Diaspora by default"] = "Отправлять в Diaspora по умолчанию";
$a->strings["Diaspora settings updated."] = "Настройки Diaspora обновлены.";
$a->strings["Diaspora connector disabled."] = "Коннектор Diaspora отключён.";
