<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'Hier können die systemweiten Einstellungen für beliebte Kanäle und solche, die automatisch betreten werden sollen, festgelegt werden. Achtung: Die Einstellungen, die hier getroffen werden, können von eingeloggten Nutzern für sich überschrieben werden.';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Kanäle, die automatisch betreten werden (mit Komma getrennt)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'Liste von Chaträumen, die automatisch betreten werden sollen, wenn die App gestartet wird.';
$a->strings['Popular Channels (comma separated)'] = 'Beliebte Kanäle (mit Komma getrennt)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'Liste populärer Chaträume, die zum schnellen Betreten am Seitenrand aufgelistet und verlinkt werden.';
$a->strings['IRC Settings'] = 'IRC-Einstellungen';
$a->strings['IRC Chatroom'] = 'IRC-Chatraum';
$a->strings['Popular Channels'] = 'Beliebte Räume';
$a->strings['Save Settings'] = 'Einstellungen speichern';
