<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Engedély megtagadva.';
$a->strings['Unable to register the client at the pump.io server \'%s\'.'] = 'Nem lehet regisztrálni a klienst a(z) „%s” pump.io kiszolgálón.';
$a->strings['You are now authenticated to pumpio.'] = 'Most már hitelesítve van a pump.io-hoz.';
$a->strings['return to the connector page'] = 'visszatérés a csatlakozó oldalára';
$a->strings['Post to pumpio'] = 'Beküldése a pump.io-ra';
$a->strings['Pump.io Import/Export/Mirror'] = 'Pump.io importálás, exportálás vagy tükrözés';
$a->strings['pump.io username (without the servername)'] = 'pump.io felhasználónév (a kiszolgálónév nélkül)';
$a->strings['pump.io servername (without "http://" or "https://" )'] = 'pump.io kiszolgálónév (a „http://” vagy „https://” nélkül)';
$a->strings['Authenticate your pump.io connection'] = 'A pump.io-kapcsolatának hitelesítése';
$a->strings['Import the remote timeline'] = 'A távoli idővonal importálása';
$a->strings['Enable pump.io Post Addon'] = 'A pump.io-beküldő bővítmény engedélyezése';
$a->strings['Post to pump.io by default'] = 'Beküldés a pump.io-ra alapértelmezetten';
$a->strings['Should posts be public?'] = 'Nyilvánosak legyenek a bejegyzések?';
$a->strings['Mirror all public posts'] = 'Összes nyilvános bejegyzés tükrözése';
$a->strings['Check to delete this preset'] = 'Jelölje be az előbeállítás törléséhez';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['status'] = 'állapotát';
$a->strings['%1$s likes %2$s\'s %3$s'] = '%1$s kedveli %2$s %3$s';
