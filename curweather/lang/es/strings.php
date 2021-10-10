<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Error fetching weather data. Error was: '] = 'Error al obtener datos meteorológicos. El error fue:';
$a->strings['Current Weather'] = 'Clima actual';
$a->strings['Relative Humidity'] = 'Humedad relativa';
$a->strings['Pressure'] = 'Presión';
$a->strings['Wind'] = 'Viento';
$a->strings['Last Updated'] = 'Última actualización';
$a->strings['Data by'] = 'Información por';
$a->strings['Show on map'] = 'Mostrar en mapa';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'Hubo un problema al acceder a la información del clima. Pero eche un vistazo';
$a->strings['at OpenWeatherMap'] = 'en OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'No se encontró APPID, por favor contacte con su administrador para obtener una.';
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['Settings'] = 'Ajustes';
$a->strings['Enter either the name of your location or the zip code.'] = 'Introduzca el nombre de su ubicación o el código postal.';
$a->strings['Your Location'] = 'Su ubicación';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'Identificador de su ubicación (nombre o código postal), ej. <em>Berlin,DE</em> o <em>14476,DE</em>.';
$a->strings['Units'] = 'Unidades';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'seleccionar si la temperatura debería ser mostrada en &deg;C o &deg;F';
$a->strings['Show weather data'] = 'Mostrar información de clima';
$a->strings['Caching Interval'] = 'Obteniendo intervalo';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = '¿Durante cuánto tiempo debería ser obtenida la información de clima? Eliga de acuerdo a su tipo de cuenta de OpenWeatherMap.';
$a->strings['no cache'] = 'sin almacenamiento';
$a->strings['minutes'] = 'minutos';
$a->strings['Your APPID'] = 'Su APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Su clave API provista por OpenWeatherMap';
