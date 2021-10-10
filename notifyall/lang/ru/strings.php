<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Send email to all members'] = 'Послать почту всем пользователям';
$a->strings['%s Administrator'] = '%s Администратор';
$a->strings['%1$s, %2$s Administrator'] = '%1$s, %2$s Администратор';
$a->strings['No recipients found.'] = 'Получатели не найдены.';
$a->strings['Emails sent'] = 'Сообщения высланы.';
$a->strings['Send email to all members of this Friendica instance.'] = 'Выслать почтовое сообщение всем пользователям этого узла Friendica.';
$a->strings['Message subject'] = 'Тема сообщения';
$a->strings['Test mode (only send to administrator)'] = 'Пробный режим (отправить только администратору)';
$a->strings['Submit'] = 'Отправить';
