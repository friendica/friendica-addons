<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Error fetching weather data.\\nError was: "] = "Säätietojen noutamisessa tapahtui virhe.\\nLisätietoja: ";
$a->strings["Current Weather"] = "Sää";
$a->strings["Relative Humidity"] = "Suhteellinen kosteus";
$a->strings["Pressure"] = "Ilmanpaine";
$a->strings["Wind"] = "Tuuli";
$a->strings["Last Updated"] = "Viimeksi päivitetty";
$a->strings["Data by"] = "Tiedot tuottaa";
$a->strings["Show on map"] = "Näytä kartalla";
$a->strings["There was a problem accessing the weather data. But have a look"] = "Säätietohaussa tapahtui virhe. Voit kuitenkin katsoa";
$a->strings["at OpenWeatherMap"] = "OpenWeatherMappiä";
$a->strings["Current Weather settings updated."] = "Sääasetukset päivitetty.";
$a->strings["No APPID found, please contact your admin to obtain one."] = "APPID puuttuu, ota yhteyttä ylläpitäjään.";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Settings"] = "Asetukset";
$a->strings["Enter either the name of your location or the zip code."] = "Syötä sijaintisi nimi tai postinumero.";
$a->strings["Your Location"] = "Sijaintisi";
$a->strings["Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>."] = "Sijantisi (paikka tai postinumero), esim. <em>Helsinki,FI</em> tai <em>00100,FI</em>.";
$a->strings["Units"] = "Yksiköt";
$a->strings["select if the temperature should be displayed in &deg;C or &deg;F"] = "ota käyttöön Celsius-asteikko (&deg;C) tai Fahrenheit-asteikko (&deg;F)";
$a->strings["Show weather data"] = "Näytä säätiedot";
$a->strings["Curweather settings saved."] = "Curweather -asetukset tallennettu.";
$a->strings["Caching Interval"] = "Välimuistin aikaväli";
$a->strings["no cache"] = "Ei välimuistia";
$a->strings["minutes"] = "minuuttia";
$a->strings["Your APPID"] = "Sinun APPID";
$a->strings["Your API key provided by OpenWeatherMap"] = "API-avain OpenWeatherMapiltä";
