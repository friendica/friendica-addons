<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Redirect URL"] = "Ссылка для перенаправления";
$a->strings["all your visitors from the web will be redirected to this URL"] = "все посетители будут перенаправлены на этот URL";
$a->strings["Begin of the Blackout"] = "Начало блэкаута";
$a->strings["format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute"] = "формат: <em>YYYY</em> год, <em>MM</em> месяц, <em>DD</em> день, <em>hh</em> час и <em>mm</em> минута";
$a->strings["End of the Blackout"] = "Конец блэкаута";
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this."] = "Указана более ранняя дата окончания, чем дата начала. Это надо исправить.";
