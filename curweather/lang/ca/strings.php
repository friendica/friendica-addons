<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Error fetching weather data.\\nError was: "] = "S'ha produït un error en recuperar les dades meteorològiques. \\\\ NEror ha estat:";
$a->strings["Current Weather"] = "Temps actual";
$a->strings["Relative Humidity"] = "Humitat relativa";
$a->strings["Pressure"] = "pressió";
$a->strings["Wind"] = "vent";
$a->strings["Last Updated"] = "Última actualització";
$a->strings["Data by"] = "Dades de";
$a->strings["Show on map"] = "Mostra al mapa";
$a->strings["There was a problem accessing the weather data. But have a look"] = "S'ha produït un problema en accedir a les dades meteorològiques. Però mireu-ho";
$a->strings["at OpenWeatherMap"] = "a OpenWeatherMap";
$a->strings["Current Weather settings updated."] = "S'ha actualitzat la configuració meteorològica actual.";
$a->strings["No APPID found, please contact your admin to obtain one."] = "No s'ha trobat cap APPID. Poseu-vos en contacte amb l'administrador per obtenir-ne una.";
$a->strings["Save Settings"] = "Desa la configuració";
$a->strings["Settings"] = "Configuració";
$a->strings["Enter either the name of your location or the zip code."] = "Introduïu el nom de la vostra ubicació o el codi postal.";
$a->strings["Your Location"] = "La teva localització";
$a->strings["Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>."] = "Identificador de la vostra ubicació (nom o codi postal), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.";
$a->strings["Units"] = "unitat";
$a->strings["select if the temperature should be displayed in &deg;C or &deg;F"] = "seleccioneu si la temperatura ha de mostrar-se en ° C o ° F";
$a->strings["Show weather data"] = "Mostra dades meteorològiques";
$a->strings["Curweather settings saved."] = "S'han desat els paràmetres de Curweather.";
$a->strings["Caching Interval"] = "Interval de cau";
$a->strings["For how long should the weather data be cached? Choose according your OpenWeatherMap account type."] = "Per quant temps s’han de mantenir en memòria cau les dades meteorològiques? Trieu segons el vostre tipus de compte OpenWeatherMap.";
$a->strings["no cache"] = "no cau";
$a->strings["minutes"] = "minuts";
$a->strings["Your APPID"] = "La vostra APPID";
$a->strings["Your API key provided by OpenWeatherMap"] = "La vostra clau d’API proporcionada per OpenWeatherMap";
