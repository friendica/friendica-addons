<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['WebRTC Videochat'] = 'WebRTC Videochat';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['WebRTC Base URL'] = 'Basis-URL des WebRTC-Servers';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'Auf welcher Seite sollten deine Nutzer einen WebRTC-Chatraum anlegen? Du könntest bspw. https://live.mayfirst.org verwenden.';
$a->strings['Video Chat'] = 'Video Chat';
$a->strings['WebRTC is a video and audio conferencing tool that works in all modern browsers. Just create a new chat room and send the link to someone you want to chat with.'] = 'WebRTC ist ein Standart für Audio und Video Konferenzen der von allen modernen Browser unterstützt wird, Öffne einfach einen neuen Chatraum und sende den Link an diejenige Person, mit der du dich unterhalten möchtest.';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Bitte schicke eine Erinnerung an deinen Friendica-Admin, dass WebRTC noch nicht richtig konfiguriert ist.';
