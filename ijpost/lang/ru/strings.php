<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to Insanejournal'] = 'Отправить в Insanejournal';
$a->strings['Enable InsaneJournal Post Addon'] = 'Включить дополнение для InsaneJournal';
$a->strings['InsaneJournal username'] = 'Имя пользователя InsaneJournal';
$a->strings['InsaneJournal password'] = 'Пароль в InsaneJournal';
$a->strings['Post to InsaneJournal by default'] = 'Отправлять записи в InsaneJournal по-умолчанию';
$a->strings['InsaneJournal Export'] = 'Экспорт InsaneJournal';
