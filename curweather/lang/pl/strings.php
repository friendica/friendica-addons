<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Error fetching weather data.\\nError was: "] = "Błąd podczas pobierania danych pogodowych. \\nError był:";
$a->strings["Current Weather"] = "Obecna pogoda";
$a->strings["Relative Humidity"] = "Względna wilgotność";
$a->strings["Pressure"] = "Ciśnienie";
$a->strings["Wind"] = "Wiatr";
$a->strings["Last Updated"] = "Ostatnio zaktualizowano";
$a->strings["Data by"] = "Dane wg";
$a->strings["Show on map"] = "Pokaż na mapie";
$a->strings["There was a problem accessing the weather data. But have a look"] = "Wystąpił problem z dostępem do danych pogodowych. Ale spójrz";
$a->strings["at OpenWeatherMap"] = "w OpenWeatherMap";
$a->strings["Current Weather settings updated."] = "Zaktualizowano bieżące ustawienia pogody.";
$a->strings["No APPID found, please contact your admin to obtain one."] = "Nie znaleziono APPID, skontaktuj się z administratorem, aby go uzyskać.";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Settings"] = "Ustawienia";
$a->strings["Enter either the name of your location or the zip code."] = "Wprowadź nazwę swojej lokalizacji lub kod pocztowy.";
$a->strings["Your Location"] = "Twoja lokalizacja";
$a->strings["Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>."] = "Identyfikator Twojej lokalizacji (nazwa lub kod pocztowy), np. <em>Lublin, LU</em> lub <em>20001, LU</em>.";
$a->strings["Units"] = "Jednostka";
$a->strings["select if the temperature should be displayed in &deg;C or &deg;F"] = "wybierz, czy temperatura powinna być wyświetlana w &deg;C lub &deg;F";
$a->strings["Show weather data"] = "Pokaż dane pogodowe";
$a->strings["Curweather settings saved."] = "Ustawienia pogodowe zostały zapisane.";
$a->strings["Caching Interval"] = "Interwał buforowania";
$a->strings["For how long should the weather data be cached? Choose according your OpenWeatherMap account type."] = "Od jak dawna powinny być buforowane dane pogodowe? Wybierz zgodnie z typem konta OpenWeatherMap.";
$a->strings["no cache"] = "Brak pamięci podręcznej";
$a->strings["minutes"] = "minut";
$a->strings["Your APPID"] = "Twój APPID";
$a->strings["Your API key provided by OpenWeatherMap"] = "Twój klucz API dostarczony przez OpenWeatherMap";
