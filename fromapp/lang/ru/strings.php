<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Fromapp settings updated.'] = 'Настройки Fromapp обновлены.';
$a->strings['FromApp Settings'] = 'Настройки Fromapp';
$a->strings['The application name you would like to show your posts originating from.'] = 'Имя приложения, которое будет показываться при просмотре сообщения.';
$a->strings['Use this application name even if another application was used.'] = 'Использовать это имя приложения даже если используется другое приложение.';
$a->strings['Submit'] = 'Добавить';
