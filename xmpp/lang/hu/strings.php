<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['XMPP-Chat (Jabber)'] = 'XMPP-csevegés (Jabber)';
$a->strings['Enable Webchat'] = 'Webes csevegés engedélyezése';
$a->strings['Individual Credentials'] = 'Egyéni hitelesítési adatok';
$a->strings['Jabber BOSH host'] = 'Jabber BOSH kiszolgáló';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Use central userbase'] = 'Központi felhasználóbázis használata';
$a->strings['If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.'] = 'Ha engedélyezve van, akkor a felhasználók automatikusan be fognak jelentkezni egy ejabberd-kiszolgálóra, amelyet erre a számítógépre kell telepíteni az „auth_ejabberd.php” parancsfájlon keresztül szinkronizált hitelesítési adatokkal.';
