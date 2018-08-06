<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Permission denied."] = "Odmowa dostępu.";
$a->strings["Unable to register the client at the pump.io server '%s'."] = "Nie można zarejestrować klienta na serwerze pump.io '%s'.";
$a->strings["You are now authenticated to pumpio."] = "Jesteś teraz uwierzytelniony w trybie pumpio.";
$a->strings["return to the connector page"] = "powrót do strony łączenia";
$a->strings["Post to pumpio"] = "Prześlij do pumpio";
$a->strings["Pump.io Import/Export/Mirror"] = "Pump.io Import/Export/Mirror";
$a->strings["pump.io username (without the servername)"] = "nazwa użytkownika pump.io (bez nazwy serwera)";
$a->strings["pump.io servername (without \"http://\" or \"https://\" )"] = "pump.io nazwa_serwera (bez \"http://\" lub \"https://\")";
$a->strings["Authenticate your pump.io connection"] = "Uwierzytelnij swoje połączenie pump.io";
$a->strings["Import the remote timeline"] = "Zaimportuj zdalną oś czasu";
$a->strings["Enable pump.io Post Addon"] = "Włącz dodatek pump.io";
$a->strings["Post to pump.io by default"] = "Opublikuj domyślnie w pump.io";
$a->strings["Should posts be public?"] = "Czy posty powinny być publiczne?";
$a->strings["Mirror all public posts"] = "Odbij wszystkie publiczne posty";
$a->strings["Check to delete this preset"] = "Zaznacz, aby usunąć to ustawienie wstępne";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Pump.io post failed. Queued for retry."] = "Błąd Pump.io post. W kolejce do ponowienia.";
$a->strings["Pump.io like failed. Queued for retry."] = "Błąd Pump.io. W kolejce do ponowienia.";
$a->strings["status"] = "status";
$a->strings["%1\$s likes %2\$s's %3\$s"] = "%1\$slubi %2\$s %3\$s ";
