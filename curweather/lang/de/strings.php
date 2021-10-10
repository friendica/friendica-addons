<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Error fetching weather data. Error was: '] = 'Fehler beim Abrufen der Wetterdaten. Die Fehlermeldung lautet:';
$a->strings['Current Weather'] = 'Aktuelles Wetter';
$a->strings['Relative Humidity'] = 'Relative Luftfeuchtigkeit';
$a->strings['Pressure'] = 'Luftdruck';
$a->strings['Wind'] = 'Wind';
$a->strings['Last Updated'] = 'Letzte Aktualisierung';
$a->strings['Data by'] = 'Daten von';
$a->strings['Show on map'] = 'Karte anzeigen';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'Es gab ein Problem beim Abrufen der Wetterdaten. Aber wirf doch mal einen Blick';
$a->strings['at OpenWeatherMap'] = 'auf OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'Keine APPID gefunden, bitte kontaktiere deinen Admin, damit eine eingerichtet wird.';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Settings'] = 'Einstellungen';
$a->strings['Enter either the name of your location or the zip code.'] = 'Gib entweder den Namen oder die PLZ deines Ortes ein.';
$a->strings['Your Location'] = 'Deinen Standort festlegen';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'Identifikator deines Standorts (Name oder Postleitzahl), z.B. <em>Berlin,DE</em> oder <em>14476,DE</em>.';
$a->strings['Units'] = 'Einheiten';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'wähle, ob die Temperatur in &deg;C oder &deg;F angezeigt werden soll';
$a->strings['Show weather data'] = 'Zeige Wetterdaten';
$a->strings['Caching Interval'] = 'Cache-Intervall';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'Wie lange sollen die Wetterdaten zwischengespeichert werden? Wähle eine für deinen OpenWeatherMap-Account passende Einstellung.';
$a->strings['no cache'] = 'kein Cache';
$a->strings['minutes'] = 'Minuten';
$a->strings['Your APPID'] = 'Deine APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Der API-Schlüssel von OpenWeatherMap';
