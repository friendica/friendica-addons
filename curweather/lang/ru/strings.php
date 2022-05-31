<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Error fetching weather data. Error was: '] = 'Ошибка получения данных о погоде. Ошибка:';
$a->strings['Current Weather'] = 'Погода сейчас';
$a->strings['Relative Humidity'] = 'Относительная влажность';
$a->strings['Pressure'] = 'Давление';
$a->strings['Wind'] = 'Ветер';
$a->strings['Last Updated'] = 'Обновлено';
$a->strings['Data by'] = 'Данные из';
$a->strings['Show on map'] = 'Показать на карте';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'Не получилось получить данные о погоде. Но вы можете посмотреть';
$a->strings['at OpenWeatherMap'] = 'OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'Не найден APPID, свяжитесь с вашим администратором, чтобы получить его.';
$a->strings['Enter either the name of your location or the zip code.'] = 'Введите ваше местоположение или индекс.';
$a->strings['Your Location'] = 'Ваше местонахождение';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'Ваше местоположение (имя или индекс), например <em>Berlin,DE</em> или <em>14476,DE</em>.';
$a->strings['Units'] = 'Единицы';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'выберите как должна отображаться температура - в &deg;C или &deg;F';
$a->strings['Show weather data'] = 'Показать данные о погоде';
$a->strings['Current Weather Settings'] = 'Текущие настройки';
$a->strings['Save Settings'] = 'Сохранить настройки';
$a->strings['Caching Interval'] = 'Интервал кэширования';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'Как долго кэшировать данные о погоде? Выберите в соответствии с типом вашей учётной записи OpenWeatherMap.';
$a->strings['no cache'] = 'не кэшировать';
$a->strings['minutes'] = 'мин.';
$a->strings['Your APPID'] = 'Ваш APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Ваш ключ API, полученный у OpenWeatherMap';
