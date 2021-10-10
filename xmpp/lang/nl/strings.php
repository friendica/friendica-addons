<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['XMPP settings updated.'] = 'XMPP-instellingen opgeslagen';
$a->strings['XMPP-Chat (Jabber)'] = 'XMPP-chat (Jabber)';
$a->strings['Enable Webchat'] = 'Webchat inschakelen';
$a->strings['Individual Credentials'] = 'Individuele inloggegevens';
$a->strings['Jabber BOSH host'] = 'Jabber BOSH Server';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['Use central userbase'] = 'Gebruik centrale gebruikersbank';
$a->strings['If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.'] = 'Wanneer ingeschakeld zullen gebruikers automatisch inloggen op een ejabberd-server die op deze server moet geïnstalleerd staan, met dezelfde gebruikersnaam en wachtwoord, via het "auth_ejabberd.php" script.';
$a->strings['Settings updated.'] = 'Instellingen opgeslagen';
