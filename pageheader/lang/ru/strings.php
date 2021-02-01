<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Message"] = "Сообщение";
$a->strings["Message to display on every page on this server (or put a pageheader.html file in your docroot)"] = "Сообщение для отображения на каждой странице этого сервера (или поместите файл pageheader.html в корневую папку веб-сервера)";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["pageheader Settings saved."] = "pageheader Настройки сохранены.";
