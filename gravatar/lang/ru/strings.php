<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["generic profile image"] = "общее изображение профиля";
$a->strings["random geometric pattern"] = "случайный геометрический паттерн";
$a->strings["monster face"] = "monster face";
$a->strings["computer generated face"] = "сгенерированное лицо";
$a->strings["retro arcade style face"] = "лицо в стиле retro arcade";
$a->strings["Information"] = "Информация";
$a->strings["Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar."] = "Аддон Libravatar тоже установлен. Пожалуйста, отключите его.<br>Libravatar использует Gravatar если Libravatar не может найти изображение у себя.";
$a->strings["Submit"] = "Добавить";
$a->strings["Default avatar image"] = "Аватар по умолчанию";
$a->strings["Select default avatar image if none was found at Gravatar. See README"] = "Выберите аватар по умолчанию если ничего не было найдено на Gravatar. Смотрите README";
$a->strings["Rating of images"] = "Рейтинг изображений";
$a->strings["Select the appropriate avatar rating for your site. See README"] = "Выберите нужный рейтинг изображений для вашего сайта. Смотрите README";
$a->strings["Gravatar settings updated."] = "Настройки Gravatar обновлены.";
