<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Group Text settings updated."] = "Настройки Group Text обновлены.";
$a->strings["Group Text"] = "Group Text";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Используйте текстовый (не изображение) селектор группы в режиме редактирования группы";
$a->strings["Submit"] = "Добавить";
