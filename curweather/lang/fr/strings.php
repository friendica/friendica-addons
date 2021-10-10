<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Error fetching weather data. Error was: '] = 'Erreur de récupération des données météo. L\'erreur était :';
$a->strings['Current Weather'] = 'Météo actuelle';
$a->strings['Relative Humidity'] = 'Humidité relative';
$a->strings['Pressure'] = 'Pression';
$a->strings['Wind'] = 'Vent';
$a->strings['Last Updated'] = 'Dernière mise-à-jour';
$a->strings['Data by'] = 'Données de';
$a->strings['Show on map'] = 'Montrer sur la carte';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'Une erreur est survenue lors de l\'accès aux données météo. Vous pouvez quand même jeter un oeil';
$a->strings['at OpenWeatherMap'] = 'à OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'Pas d\'APPID trouvé, merci de contacter votre administrateur pour en obtenir un.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Settings'] = 'Paramètres';
$a->strings['Enter either the name of your location or the zip code.'] = 'Entrez le nom de votre emplacement ou votre code postal.';
$a->strings['Your Location'] = 'Votre position';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'Identifiant de votre emplacement (nom ou code postal), par exemple <em>Paris 08, Fr</em> ou <em>75008, FR</em>.';
$a->strings['Units'] = 'Unités';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'Sélectionner si la température devrait être affichée en °C ou en °F';
$a->strings['Show weather data'] = 'Montrer les données météos';
$a->strings['Caching Interval'] = 'Intervalle de mise en cache.';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'Pendant combien de temps les données météo doivent-elles être mises en cache? Choisissez en fonction du type de compte OpenWeatherMap.';
$a->strings['no cache'] = 'pas de cache';
$a->strings['minutes'] = 'minutes';
$a->strings['Your APPID'] = 'Votre APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Votre clé pour l\'API de OpenWeatherMap';
