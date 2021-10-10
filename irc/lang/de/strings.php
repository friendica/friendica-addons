<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['IRC Settings'] = 'IRC Einstellungen';
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'Hier könne die systemweiten Einstellungen für beliebte Kanäle und solche die automatisch betreten werden sollen festgelegt werden. Achtung: Die Einstellungen die her getroffen werden können von eingeloggten Nutzern für sich überschrieben werden.';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'mit diesen Kanälen soll man automatisch verbunden werden (Komma getrennt)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'Liste von Chaträumen die automatisch betreten werden sollen wenn die App gestartet wurde.';
$a->strings['Popular Channels (comma separated)'] = 'Beliebte Kanäle (mit Komma getrennt)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'Liste populärer Chaträume die vverlinkt am Seitenrand aufgelistet werden zum schnelleren betreten.';
$a->strings['IRC settings saved.'] = 'IRC Einstellungen gespeichert.';
$a->strings['IRC Chatroom'] = 'IRC Chatraum';
$a->strings['Popular Channels'] = 'Beliebte Räume';
