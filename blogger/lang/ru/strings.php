<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to blogger'] = 'Написать в Blogger';
$a->strings['Blogger Export'] = 'Экспорт в Blogger';
$a->strings['Enable Blogger Post Addon'] = 'Включить аддон репоста в Blogger';
$a->strings['Blogger username'] = 'Имя пользователя Blogger';
$a->strings['Blogger password'] = 'Пароль Blogger';
$a->strings['Blogger API URL'] = 'Blogger API URL';
$a->strings['Post to Blogger by default'] = 'Отправлять в Blogger по умолчанию';
$a->strings['Save Settings'] = 'Сохранить настройки';
$a->strings['Post from Friendica'] = 'Сообщение от Friendica';
