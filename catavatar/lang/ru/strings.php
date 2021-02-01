<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Use Cat as Avatar"] = "Поставить кота на аватар";
$a->strings["More Random Cat!"] = "Сгенерировать ещё котов!";
$a->strings["Reset to email Cat"] = "Сбросить на кота по-умолчанию";
$a->strings["Cat Avatar Settings"] = "Настройки Cat Avatar";
$a->strings["The cat hadn't found itself."] = "Кот не нашёл сам себя.";
$a->strings["There was an error, the cat ran away."] = "Возникла ошибка, кот убежал.";
$a->strings["Profile Photos"] = "Фото профиля";
$a->strings["Meow!"] = "Мяу!";
