<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Permission denied.'] = 'Odmowa dostępu.';
$a->strings['Unable to register the client at the pump.io server \'%s\'.'] = 'Nie można zarejestrować klienta na serwerze pump.io \'%s\'.';
$a->strings['You are now authenticated to pumpio.'] = 'Jesteś teraz uwierzytelniony w trybie pumpio.';
$a->strings['return to the connector page'] = 'powrót do strony łączenia';
$a->strings['Post to pumpio'] = 'Prześlij do pumpio';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Delete this preset'] = 'Usuń to ustawienie wstępne';
$a->strings['Authenticate your pump.io connection'] = 'Uwierzytelnij swoje połączenie pump.io';
$a->strings['Pump.io servername (without "http://" or "https://" )'] = 'Nazwa serwera Pump.io (bez "http://" lub "https://")';
$a->strings['Pump.io username (without the servername)'] = 'Nazwa użytkownika Pump.io (bez nazwy serwera)';
$a->strings['Import the remote timeline'] = 'Zaimportuj zdalną oś czasu';
$a->strings['Enable Pump.io Post Addon'] = 'Włącz dodatek Pump.io';
$a->strings['Post to Pump.io by default'] = 'Publikuj domyślnie w Pump.io';
$a->strings['Should posts be public?'] = 'Czy posty powinny być publiczne?';
$a->strings['Mirror all public posts'] = 'Odbij wszystkie publiczne posty';
$a->strings['Pump.io Import/Export/Mirror'] = 'Pump.io Import/Export/Mirror';
$a->strings['status'] = 'status';
$a->strings['%1$s likes %2$s\'s %3$s'] = '%1$slubi %2$s %3$s ';
