<?php

if(! function_exists("string_plural_select_da_DK")) {
function string_plural_select_da_DK($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Error fetching weather data. Error was: '] = 'Fejl i indlæsning af vejrdata. Fejlen var: ';
$a->strings['Current Weather'] = 'Nuværende vejr';
$a->strings['Relative Humidity'] = 'Relativ fugtighed';
$a->strings['Pressure'] = 'Tryk';
$a->strings['Wind'] = 'Vind';
$a->strings['Last Updated'] = 'Senest opdateret';
$a->strings['Data by'] = 'Data af';
$a->strings['Show on map'] = 'Vis på kort';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'Der var et problem med at tilgå vejrdata. Men tag et kig.';
$a->strings['at OpenWeatherMap'] = 'Hos OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'Intet APPID fundet, kontakt venligst din administrator for at få fat i ét.';
$a->strings['Enter either the name of your location or the zip code.'] = 'Indtast enten navnet på din placering eller postnummeret.';
$a->strings['Your Location'] = 'Din placering';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'Identifikationer på din placering (navn eller postnummer), såsom. <em>Copenhagen,DK</em> eller <em>2025,DK</em>.';
$a->strings['Units'] = 'Enheder';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'Vælg om temperaturen skal vises i &deg;C eller &deg;F';
$a->strings['Show weather data'] = 'Vis vejrdata';
$a->strings['Current Weather Settings'] = 'Nuværende vejrindstillinger';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Caching Interval'] = 'Caching-interval';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'Hvor længe skal vejrdata være cachet? Vælg baseret på din OpenWeatherMap-kontotype.';
$a->strings['no cache'] = 'ingen cache';
$a->strings['minutes'] = 'minutter';
$a->strings['Your APPID'] = 'Dit APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Din API-nøgle tildelt fra OpenWeatherMap';
