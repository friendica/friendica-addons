<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Lupa kielletty.';
$a->strings['Post to pumpio'] = 'Lähetä pump.ioon';
$a->strings['pump.io username (without the servername)'] = 'pump.io käyttäjätunnus (ilman palvelinnimeä)';
$a->strings['pump.io servername (without "http://" or "https://" )'] = 'pump.io palvelinnimi (ilman "http://" tai "https://" )';
$a->strings['Import the remote timeline'] = 'Tuo etäaikajana';
$a->strings['Enable pump.io Post Addon'] = 'Ota pump.io -viestilisäosa käyttöön';
$a->strings['Post to pump.io by default'] = 'Lähetä pump.iohon oletuksena';
$a->strings['Mirror all public posts'] = 'Peilaa kaikki julkiset julkaisut';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Pump.io post failed. Queued for retry.'] = 'Pump.io -julkaisu epäonnistui. Jonossa uudelleenyritykseen.';
$a->strings['Pump.io like failed. Queued for retry.'] = 'Pump.io -tykkäys epäonnistui. Jonossa uudelleenyritykseen.';
$a->strings['status'] = 'tila';
