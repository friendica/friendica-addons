<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Error fetching weather data.\nError was: '] = 'Fout bij het ophalen van de weer data:\nFout was:';
$a->strings['Current Weather'] = 'Het weer';
$a->strings['Relative Humidity'] = 'Relatieve vochtigheid';
$a->strings['Pressure'] = 'Luchtdruk';
$a->strings['Wind'] = 'Wind';
$a->strings['Last Updated'] = 'Laatste wijziging';
$a->strings['Data by'] = 'Data afkomstig van';
$a->strings['Show on map'] = 'Toon op kaart';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'Er was een probleem met het ophalen van de data. Bekijk het';
$a->strings['at OpenWeatherMap'] = 'Op OpenWeatherMap';
$a->strings['Current Weather settings updated.'] = 'Huidige weerinstellingen opgeslagen';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'Geen APPID gevonden. Contacteer je node-admin om dit te verkrijgen';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['Settings'] = 'Instellingen';
$a->strings['Enter either the name of your location or the zip code.'] = 'Voor de naam of de postcode van je locatie in';
$a->strings['Your Location'] = 'Uw locatie';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'ID van je locatie (naam of postocde), vb. <em>Amsterdam, NL</em> of <em>2000,BE</em>';
$a->strings['Units'] = 'Eenheden';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'Selecteer of de temperatuur in &deg;C of &deg;F moet weergegeven worden';
$a->strings['Show weather data'] = 'Toon weer ';
$a->strings['Curweather settings saved.'] = 'Curweather instellingen opgeslagen';
$a->strings['Caching Interval'] = 'Caching interval';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'Voor hoe lang moet de weer data opgeslagen worden? Kies dit volgens je OpenWeatherMap account type.';
$a->strings['no cache'] = 'Geen cache';
$a->strings['minutes'] = 'Minuten';
$a->strings['Your APPID'] = 'Uw APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Je OpenWeatherMap API-Key';
