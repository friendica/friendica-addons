<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Markdown"] = "Markdown";
$a->strings["Enable Markdown parsing"] = "Включить форматирование Markdown";
$a->strings["If enabled, self created items will additionally be parsed via Markdown."] = "Если включено, то созданные вами записи будут дополнительно форматироваться в соответствии с разметкой Markdown.";
$a->strings["Save Settings"] = "Сохранить настройки";
