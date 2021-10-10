<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Filtered by rule: %s'] = 'Отфильтровано по правилу: %s';
$a->strings['Advanced Content Filter'] = 'Расширенный фильтр содержимого';
$a->strings['Back to Addon Settings'] = 'Вернуться к настройкам дополнений';
$a->strings['Add a Rule'] = 'Добавить правило';
$a->strings['Help'] = 'Помощь';
$a->strings['Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href="advancedcontentfilter/help">help page</a>.'] = 'Добавляйте и управляйте своими собственными фильтрами на этой странице. У правил есть имя и произвольное выражение, которое будет сопоставляться с данными поста. Для полной ссылки на доступные операции и переменные, посмотрите <a href="advancedcontentfilter/help">страницу справки</a>.';
$a->strings['Your rules'] = 'Ваши правила';
$a->strings['You have no rules yet! Start adding one by clicking on the button above next to the title.'] = 'У Вас нет ни одного правила. Начните добавление первого с нажатия на кнопку выше, рядом с названием.';
$a->strings['Disabled'] = 'Отключено';
$a->strings['Enabled'] = 'Включено';
$a->strings['Disable this rule'] = 'Отключить это правило';
$a->strings['Enable this rule'] = 'Включить это правило';
$a->strings['Edit this rule'] = 'Изменить это правило';
$a->strings['Edit the rule'] = 'Изменить правило';
$a->strings['Save this rule'] = 'Сохранить это правило';
$a->strings['Delete this rule'] = 'Удалить это правило';
$a->strings['Rule'] = 'Правило';
$a->strings['Close'] = 'Закрыть';
$a->strings['Add new rule'] = 'Добавить новое правило';
$a->strings['Rule Name'] = 'Название правила';
$a->strings['Rule Expression'] = 'Содержание правила';
$a->strings['<p>Examples:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tags</li></ul>'] = '<p>Примеры:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tags</li></ul>';
$a->strings['Cancel'] = 'Отмена';
$a->strings['You must be logged in to use this method'] = 'Вы должны авторизоваться для использования этого метода';
$a->strings['Invalid form security token, please refresh the page.'] = 'Неверный ключ, пожалуйста, перезагрузите страницу';
$a->strings['The rule name and expression are required.'] = 'Требуется ввести название и значение правила.';
$a->strings['Rule successfully added'] = 'Правило успешно добавлено';
$a->strings['Rule doesn\'t exist or doesn\'t belong to you.'] = 'Правило не найдено или доступ к нему закрыт';
$a->strings['Rule successfully updated'] = 'Правило успешно обновлено';
$a->strings['Rule successfully deleted'] = 'Правило успешно удалено';
$a->strings['Missing argument: guid.'] = 'Отсутствующий аргумент: guid.';
$a->strings['Unknown post with guid: %s'] = 'Неизвестный пост в ID: %s';
$a->strings['Method not found'] = 'Метод не найден';
