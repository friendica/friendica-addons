<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "Дата начала позже даты окончания, исправьте это";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = " Пожалуйста, проверьте настройки блэкаута ещё раз. Он начнётся <strong>%s</strong> и закончится <strong>%s</strong>.";
$a->strings["Save Settings"] = "Сохранить настройки";
$a->strings["Redirect URL"] = "Ссылка для перенаправления";
$a->strings["all your visitors from the web will be redirected to this URL"] = "все посетители будут перенаправлены на этот URL";
$a->strings["Begin of the Blackout"] = "Начало блэкаута";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "Формат: <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> год, <em>MM</em> месяц, <em>DD</em> день, <em>hh</em> час и <em>mm</em> минуты.";
$a->strings["End of the Blackout"] = "Конец блэкаута";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Внимание</strong>: Переадресация будет включена после нажатия вами кнопки. Уже вошедшие пользователи <strong>не</strong> будут выброшены, но не смогут зайти снова, пока блэкаут не закончится.";
