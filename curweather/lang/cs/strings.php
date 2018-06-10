<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Error fetching weather data.\\nError was: "] = "Chyba při získávání dat o počasí.\\nChyba:";
$a->strings["Current Weather"] = "Aktuální počasí";
$a->strings["Relative Humidity"] = "Relativní vlhkost vzduchu";
$a->strings["Pressure"] = "Tlak";
$a->strings["Wind"] = "Vítr";
$a->strings["Last Updated"] = "Naposledy aktualizováno";
$a->strings["Data by"] = "Data podle";
$a->strings["Show on map"] = "Ukázat na mapě";
$a->strings["There was a problem accessing the weather data. But have a look"] = "Při získávání dat o počasí nastala chyba. Podívejte se ale";
$a->strings["at OpenWeatherMap"] = "na OpenWeatherMap";
$a->strings["Current Weather settings updated."] = "Nastavení pro Aktuální počasí aktualizováno.";
$a->strings["No APPID found, please contact your admin to obtain one."] = "Žádné APPID nebylo nalezeno, prosím kontaktujte svého administrátora pro získání APPID.";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Settings"] = "Nastavení";
$a->strings["Enter either the name of your location or the zip code."] = "Zadejte buď název místa, kde se nacházíte, nebo PSČ.";
$a->strings["Your Location"] = "Vaše poloha";
$a->strings["Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>."] = "Identifikátor vaší polohy (název nebo PSČ), např. <em>Berlin,DE</em> nebo <em>14476,DE</em>";
$a->strings["Units"] = "Jednotky";
$a->strings["select if the temperature should be displayed in &deg;C or &deg;F"] = "vyberte, jestli by se teplota měla zobrazovat v &deg;C či &deg;F";
$a->strings["Show weather data"] = "Ukázat data o počasí";
$a->strings["Curweather settings saved."] = "Nastavení Curwather uložena.";
$a->strings["Caching Interval"] = "Ukládám interval do mezipaměti";
$a->strings["For how long should the weather data be cached? Choose according your OpenWeatherMap account type."] = "Na jak dlouho by vaše data o počasí měla být uložena v mezipaměti? Vyberte podle typu vašeho účtu na OpenWeatherMap.";
$a->strings["no cache"] = "žádná mezipaměť";
$a->strings["minutes"] = "minut";
$a->strings["Your APPID"] = "Vaše APPID";
$a->strings["Your API key provided by OpenWeatherMap"] = "Váš API klíč poskytnutý OpenWetherMap";
