<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Error fetching weather data. Error was: "] = "Hiba az időjárásadatok lekérésekor. A hiba ez volt: ";
$a->strings["Current Weather"] = "Jelenlegi időjárás";
$a->strings["Relative Humidity"] = "Relatív páratartalom";
$a->strings["Pressure"] = "Légnyomás";
$a->strings["Wind"] = "Szél";
$a->strings["Last Updated"] = "Utoljára frissítve";
$a->strings["Data by"] = "Adatszolgáltató";
$a->strings["Show on map"] = "Megjelenítés térképen";
$a->strings["There was a problem accessing the weather data. But have a look"] = "Probléma történt az időjárási adatokhoz való hozzáféréskor. De nézzen körül itt:";
$a->strings["at OpenWeatherMap"] = "OpenWeatherMap";
$a->strings["No APPID found, please contact your admin to obtain one."] = "Nem található alkalmazásazonosító. Vegye fel a kapcsolatot az adminisztrátorral, hogy beszerezzen egyet.";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Settings"] = "Beállítások";
$a->strings["Enter either the name of your location or the zip code."] = "Adja meg a tartózkodási helyének a nevét vagy az irányítószámát.";
$a->strings["Your Location"] = "Az Ön tartózkodási helye";
$a->strings["Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>."] = "A tartózkodási helyének azonosítója (neve vagy irányítószáma), például <em>Budapest,HU</em> vagy <em>1234,HU</em>.";
$a->strings["Units"] = "Mértékegységek";
$a->strings["select if the temperature should be displayed in &deg;C or &deg;F"] = "Annak kiválasztása, hogy a hőmérsékletet &deg;C vagy &deg;F fokban kell megjeleníteni.";
$a->strings["Show weather data"] = "Időjárási adatok megjelenítése";
$a->strings["Caching Interval"] = "Gyorsítótárazási időköz";
$a->strings["For how long should the weather data be cached? Choose according your OpenWeatherMap account type."] = "Mennyi ideig kell az időjárási adatokat gyorsítótárazni? Válasszon az OpenWeatherMap fióktípusa szerint.";
$a->strings["no cache"] = "nincs gyorsítótár";
$a->strings["minutes"] = "perc";
$a->strings["Your APPID"] = "Az alkalmazásazonosítója";
$a->strings["Your API key provided by OpenWeatherMap"] = "Az OpenWeatherMap által biztosított API-kulcsa";
