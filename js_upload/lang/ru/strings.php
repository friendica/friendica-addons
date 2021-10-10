<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Upload a file'] = 'Загрузить файл';
$a->strings['Drop files here to upload'] = 'Перетащите сюда файлы для загрузки';
$a->strings['Cancel'] = 'Отмена';
$a->strings['Failed'] = 'Ошибка';
$a->strings['No files were uploaded.'] = 'Файлы не были загружены.';
$a->strings['Uploaded file is empty'] = 'Загруженный файл пустой.';
$a->strings['Image exceeds size limit of '] = 'Изображение превышает ограничение в';
$a->strings['File has an invalid extension, it should be one of '] = 'У файла недопустимое расширение, оно должно быть';
$a->strings['Upload was cancelled, or server error encountered'] = 'Закачка отменена, либо возникла ошибка на сервере';
