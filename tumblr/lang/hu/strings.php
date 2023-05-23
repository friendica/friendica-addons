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
$a->strings['Maximum tags'] = 'Legtöbb címke';
$a->strings['Maximum number of tags that a user can follow. Enter 0 to deactivate the feature.'] = 'A címkék legnagyobb száma, amit egy felhasználó követhet. Adjon meg 0 értéket a funkció kikapcsolásához.';
$a->strings['Post to page:'] = 'Beküldés az oldalra:';
$a->strings['(Re-)Authenticate your tumblr page'] = 'A Tumblr-oldal (újra)hitelesítése';
$a->strings['You are not authenticated to tumblr'] = 'Nincs hitelesítve van a Tumblr-hez';
$a->strings['Enable Tumblr Post Addon'] = 'A Tumblr-beküldő bővítmény engedélyezése';
$a->strings['Post to Tumblr by default'] = 'Beküldés a Tumblr-re alapértelmezetten';
$a->strings['Import the remote timeline'] = 'A távoli idővonal importálása';
$a->strings['Subscribed tags'] = 'Feliratkozott címkék';
$a->strings['Comma separated list of up to %d tags that will be imported additionally to the timeline'] = 'Legfeljebb %d címke vesszővel elválasztott listája, amelyek szintén importálásra kerülnek az idővonalon felül';
$a->strings['Tumblr Import/Export'] = 'Tumblr importálás és exportálás';
$a->strings['Post to Tumblr'] = 'Beküldése a Tumblr-re';
