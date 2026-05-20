<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Current Weather'] = 'Current Weather';
$a->strings['Relative Humidity'] = 'Relative Humidity';
$a->strings['Pressure'] = 'Pressure';
$a->strings['Wind'] = 'Wind';
$a->strings['Last Updated'] = 'Last Updated';
$a->strings['Data by'] = 'Data provided by';
$a->strings['Show on map'] = 'Show on map';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'There was a problem accessing the weather data. However, you can have a look';
$a->strings['at OpenWeatherMap'] = 'at OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'No APPID found, please contact your admin to obtain one.';
$a->strings['Enter either the name of your location or the zip code.'] = 'Enter the name or postcode of your location.';
$a->strings['Your Location'] = 'Your Location';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'Identifier of your location (name or postcode), e.g. <em>London,GB</em> or <em>SW1A 2AA</em>.';
$a->strings['Units'] = 'Units';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'Show temperature in &deg;C or &deg;F';
$a->strings['Show weather data'] = 'Show weather data';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Caching Interval'] = 'Caching Interval';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'How long should the weather data be cached for? Choose according to your OpenWeatherMap account type.';
$a->strings['no cache'] = 'no cache';
$a->strings['minutes'] = 'minutes';
$a->strings['Your APPID'] = 'Your APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Your API key provided by OpenWeatherMap';
