<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Error fetching weather data. Error was: "] = "Errore durante il recupero dei dati meteo. L'errore è stato:";
$a->strings["Current Weather"] = "Meteo";
$a->strings["Relative Humidity"] = "Umidità Relativa";
$a->strings["Pressure"] = "Pressione";
$a->strings["Wind"] = "Vento";
$a->strings["Last Updated"] = "Ultimo Aggiornamento: ";
$a->strings["Data by"] = "Data da";
$a->strings["Show on map"] = "Mostra sulla mappa";
$a->strings["There was a problem accessing the weather data. But have a look"] = "C'è stato un problema accedendo ai dati meteo, ma dai un'occhiata";
$a->strings["at OpenWeatherMap"] = "a OpenWeatherMap";
$a->strings["No APPID found, please contact your admin to obtain one."] = "APPID non trovata, contatta il tuo amministratore per averne una.";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Settings"] = "Impostazioni";
$a->strings["Enter either the name of your location or the zip code."] = "Inserisci il nome della tua posizione o il CAP";
$a->strings["Your Location"] = "La tua Posizione";
$a->strings["Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>."] = "Identificatore della tua posizione (nome o CAP), p.e. <em>Roma, IT</em> or <em>00186,IT</em>.";
$a->strings["Units"] = "Unità";
$a->strings["select if the temperature should be displayed in &deg;C or &deg;F"] = "scegli se la temperatura deve essere mostrata in °C o in °F";
$a->strings["Show weather data"] = "Mostra dati meteo";
$a->strings["Caching Interval"] = "Intervallo di cache";
$a->strings["For how long should the weather data be cached? Choose according your OpenWeatherMap account type."] = "Per quanto tempo i dati meteo devono essere memorizzati? Scegli a seconda del tuo tipo di account su OpenWeatherMap.";
$a->strings["no cache"] = "nessuna cache";
$a->strings["minutes"] = "minuti";
$a->strings["Your APPID"] = "Il tuo APPID";
$a->strings["Your API key provided by OpenWeatherMap"] = "La tua chiave API da OpenWeatherMap";
