<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings[':-)'] = ':-)';
$a->strings[':-('] = ':-(';
$a->strings['lol'] = 'lol';
$a->strings['Quick Comment Settings'] = 'Настройки быстрых комментариев';
$a->strings['Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies.'] = 'Быстрые комментарии находятся рядом с полями комментариев, иногда они скрыты. Нажмите на них, чтобы использовать готовые ответы.';
$a->strings['Enter quick comments, one per line'] = 'Введите быстрые комментарии, по одному на строку';
$a->strings['Submit'] = 'Добавить';
$a->strings['Quick Comment settings saved.'] = 'Настройки сохранены.';
