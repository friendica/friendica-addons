<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Engedély megtagadva.';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Consumer Key'] = 'Felhasználói kulcs';
$a->strings['Consumer Secret'] = 'Felhasználói titok';
$a->strings['You are now authenticated to tumblr.'] = 'Most már hitelesítve van a Tumblr-hez.';
$a->strings['return to the connector page'] = 'visszatérés a csatlakozó oldalára';
$a->strings['Post to Tumblr'] = 'Beküldése a Tumblr-re';
$a->strings['Tumblr Export'] = 'Tumblr exportálás';
$a->strings['(Re-)Authenticate your tumblr page'] = 'A Tumblr-oldal (újra)hitelesítése';
$a->strings['Enable Tumblr Post Addon'] = 'A Tumblr-beküldő bővítmény engedélyezése';
$a->strings['Post to Tumblr by default'] = 'Beküldés a Tumblr-re alapértelmezetten';
$a->strings['Post to page:'] = 'Beküldés az oldalra:';
$a->strings['You are not authenticated to tumblr'] = 'Nincs hitelesítve van a Tumblr-hez';
