<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Content Filter (NSFW and more)"] = "Фильтр контента (NSFW и прочее)";
$a->strings["This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view."] = "Это дополнение ищет указанные слова и выражения в записях и сворачивает запись, если найдёт. Это можно использовать для скрытия записей с тэгом #NSFW, просмотр которых может быть нежелателен в определённое время, например, на работе. Так же можно использовать для скрытия иного контента.";
$a->strings["Enable Content filter"] = "Включить фильтр контента";
$a->strings["Comma separated list of keywords to hide"] = "Ключевые слова для скрытия, через запятую";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Use /expression/ to provide regular expressions"] = "Используйте формат /expression/ для регулярных выражений";
$a->strings["NSFW Settings saved."] = "Настройки NSFW сохранены";
$a->strings["Filtered tag: %s"] = "Скрыт тэг: %s";
$a->strings["Filtered word: %s"] = "Скрыто слово: %s";
