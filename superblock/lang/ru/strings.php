<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Superblock'] = 'Superblock';
$a->strings['Comma separated profile URLS to block'] = 'Ссылки на блокируемые профили через запятую';
$a->strings['Save Settings'] = 'Сохранить настройки';
$a->strings['SUPERBLOCK Settings saved.'] = 'Настройки Superblock сохранены';
$a->strings['Block Completely'] = 'Заблокировать совсем';
$a->strings['superblock settings updated'] = 'настройки superblock обновлены';
